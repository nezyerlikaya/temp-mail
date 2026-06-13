<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Comment;
use App\Models\Locale;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\UserAuditEvent;
use App\Services\Localization\LocaleSettingsStore;
use App\Services\Security\AkismetSpamService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CommentModerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        app(LocaleSettingsStore::class)->ensureSeeded();
    }

    public function test_guest_comment_submission_sanitizes_content_derives_language_and_notifies_moderators(): void
    {
        $admin = User::factory()->admin()->create();
        $post = $this->postForLocale('en');

        $this->from('/blog/privacy-note')->post(route('comments.store', $post), [
            'author_name' => 'Reader One',
            'author_email' => 'reader@example.com',
            'content' => '<p>Hello <strong>team</strong></p><span style="color:red">clean note</span>',
        ])->assertRedirect('/blog/privacy-note');

        $comment = Comment::query()->firstOrFail();

        $this->assertSame($post->id, $comment->blog_post_id);
        $this->assertSame($post->locale_id, $comment->locale_id);
        $this->assertSame('pending', $comment->status);
        $this->assertSame(hash('sha256', 'reader@example.com'), $comment->author_email_hash);
        $this->assertNotNull($comment->ip_hash);
        $this->assertStringContainsString('<strong>team</strong>', $comment->content);
        $this->assertStringNotContainsString('style=', $comment->content);
        $this->assertStringNotContainsString('<span', $comment->content);

        $this->assertDatabaseHas('system_notifications', [
            'recipient_user_id' => $admin->id,
            'event_key' => 'new_pending_comment',
            'related_module' => 'content',
            'target_type' => Comment::class,
            'target_id' => $comment->id,
        ]);
    }

    public function test_executable_or_template_comment_content_is_rejected(): void
    {
        $post = $this->postForLocale('en');

        $this->from('/blog/privacy-note')->post(route('comments.store', $post), [
            'author_name' => 'Unsafe Reader',
            'author_email' => 'unsafe@example.com',
            'content' => '<script>alert(1)</script>',
        ])->assertRedirect('/blog/privacy-note')
            ->assertSessionHasErrors('content');

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_akismet_failure_is_friendly_and_does_not_block_submission(): void
    {
        $this->mock(AkismetSpamService::class, function ($mock): void {
            $mock->shouldReceive('readiness')->once()->andThrow(new \RuntimeException('Provider unavailable'));
        });

        $post = $this->postForLocale('en');

        $this->post(route('comments.store', $post), [
            'author_name' => 'Reader Two',
            'author_email' => 'reader-two@example.com',
            'content' => 'This is a thoughtful product question.',
        ])->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'author_email' => 'reader-two@example.com',
            'status' => 'pending',
            'spam_provider' => 'akismet',
            'provider_decision' => 'unavailable',
        ]);
    }

    public function test_spam_heuristics_can_route_comments_to_spam_queue(): void
    {
        User::factory()->admin()->create();
        $post = $this->postForLocale('en');

        $this->post(route('comments.store', $post), [
            'author_name' => 'Promo Bot',
            'author_email' => 'promo@example.com',
            'content' => 'casino viagra free money http://one.test http://two.test http://three.test',
        ])->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'author_email' => 'promo@example.com',
            'status' => 'spam',
        ]);
    }

    public function test_comment_moderation_queue_renders_cards_filters_and_private_metadata_for_moderators(): void
    {
        $moderator = User::factory()->create(['is_admin' => true, 'role' => 'moderator']);
        $post = $this->postForLocale('en', ['title' => 'Disposable inbox guide']);
        $pending = $this->comment($post, [
            'author_name' => 'Queue Reader',
            'author_email' => 'queue@example.com',
            'content_excerpt' => 'Pending queue question',
            'status' => 'pending',
        ]);
        $this->comment($post, [
            'author_name' => 'Spam Reader',
            'author_email' => 'spam@example.com',
            'content_excerpt' => 'Spam queue text',
            'status' => 'spam',
            'spam_score' => 95,
            'provider_decision' => 'spam',
        ]);

        $this->actingAs($moderator)
            ->get(route('admin.comment-moderation.index', ['status' => 'pending', 'q' => 'Queue']))
            ->assertOk()
            ->assertViewHas('comments', fn ($comments): bool => $comments->getCollection()->contains($pending)
                && ! $comments->getCollection()->contains('author_email', 'spam@example.com'))
            ->assertSee('Comment Moderation')
            ->assertSee('Pending queue question')
            ->assertSee('queue@example.com')
            ->assertSee('IP hash')
            ->assertSee('Approve')
            ->assertDontSee('This workspace is ready for implementation.');
    }

    public function test_normal_users_cannot_access_comment_moderation(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)
            ->get(route('admin.comment-moderation.index'))
            ->assertForbidden();
    }

    public function test_moderation_actions_approve_mark_spam_and_trash_with_audit_metadata(): void
    {
        $admin = User::factory()->admin()->create();
        $post = $this->postForLocale('en');
        $comment = $this->comment($post, [
            'author_name' => 'Action Reader',
            'author_email' => 'action@example.com',
            'content' => '<p>Keep this public note.</p>',
            'content_excerpt' => 'Keep this public note.',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.comment-moderation.approve', $comment))
            ->assertRedirect();

        $comment->refresh();
        $this->assertSame('approved', $comment->status);
        $this->assertSame($admin->id, $comment->approved_by);
        $this->assertNotNull($comment->approved_at);

        $this->actingAs($admin)
            ->post(route('admin.comment-moderation.mark', $comment), ['status' => 'spam'])
            ->assertRedirect();

        $comment->refresh();
        $this->assertSame('spam', $comment->status);
        $this->assertNull($comment->approved_by);

        $this->actingAs($admin)
            ->post(route('admin.comment-moderation.mark', $comment), ['status' => 'trashed'])
            ->assertRedirect();

        $comment->refresh();
        $this->assertSame('trashed', $comment->status);
        $this->assertNotNull($comment->trashed_at);

        $events = UserAuditEvent::query()
            ->whereIn('event', ['comment.approved', 'comment.marked_spam', 'comment.trashed'])
            ->get();

        $this->assertCount(3, $events);
        $this->assertTrue($events->every(fn ($event): bool => ($event->metadata['comment_id'] ?? null) === $comment->id));
        $this->assertStringNotContainsString('Keep this public note.', $events->pluck('metadata')->toJson());
    }

    public function test_moderators_can_reply_one_level_and_edit_with_history(): void
    {
        $moderator = User::factory()->create(['is_admin' => true, 'role' => 'moderator']);
        $post = $this->postForLocale('en');
        $comment = $this->comment($post);

        $this->actingAs($moderator)
            ->post(route('admin.comment-moderation.reply', $comment), [
                'content' => 'Thanks for the report. We will review this.',
            ])
            ->assertRedirect();

        $reply = Comment::query()->where('parent_id', $comment->id)->firstOrFail();
        $this->assertSame(1, $reply->reply_depth);
        $this->assertSame('approved', $reply->status);

        $this->actingAs($moderator)
            ->post(route('admin.comment-moderation.reply', $reply), [
                'content' => 'Nested replies should not be accepted.',
            ])
            ->assertSessionHasErrors('content');

        $this->actingAs($moderator)
            ->put(route('admin.comment-moderation.edit', $comment), [
                'content' => '<p>Edited moderator-safe content.</p>',
            ])
            ->assertRedirect();

        $comment->refresh();
        $this->assertNotNull($comment->edited_at);
        $this->assertSame($moderator->id, $comment->edited_by);
        $this->assertDatabaseHas('comment_edit_histories', [
            'comment_id' => $comment->id,
            'edited_by' => $moderator->id,
            'new_excerpt' => 'Edited moderator-safe content.',
        ]);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'comment.edited', 'target_id' => $comment->id]);
    }

    public function test_trash_restore_and_owner_admin_permanent_delete_are_enforced(): void
    {
        $moderator = User::factory()->create(['is_admin' => true, 'role' => 'moderator']);
        $admin = User::factory()->admin()->create();
        $post = $this->postForLocale('en');
        $comment = $this->comment($post);

        $this->actingAs($moderator)
            ->post(route('admin.comment-moderation.trash', $comment), ['confirm' => '1'])
            ->assertRedirect();

        $comment->refresh();
        $this->assertSame('trashed', $comment->status);
        $this->assertNotNull($comment->trashed_at);

        $this->actingAs($moderator)
            ->delete(route('admin.comment-moderation.destroy', $comment), ['confirm_delete' => '1'])
            ->assertForbidden();

        $this->actingAs($moderator)
            ->post(route('admin.comment-moderation.restore', $comment))
            ->assertRedirect();

        $this->assertSame('pending', $comment->refresh()->status);

        $this->actingAs($admin)
            ->post(route('admin.comment-moderation.trash', $comment), ['confirm' => '1'])
            ->assertRedirect();

        $this->actingAs($admin)
            ->delete(route('admin.comment-moderation.destroy', $comment), ['confirm_delete' => '1'])
            ->assertRedirect();

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'comment.deleted']);
    }

    public function test_false_positive_recovery_preserves_original_provider_decision(): void
    {
        $admin = User::factory()->admin()->create();
        $post = $this->postForLocale('en');
        $comment = $this->comment($post, [
            'status' => 'spam',
            'provider_decision' => 'spam',
            'original_provider_decision' => 'spam',
            'spam_score' => 90,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.comment-moderation.false-positive', $comment), ['status' => 'approved'])
            ->assertRedirect();

        $comment->refresh();
        $this->assertSame('approved', $comment->status);
        $this->assertSame('false_positive', $comment->manual_override);
        $this->assertSame('spam', $comment->original_provider_decision);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'comment.false_positive_restored', 'target_id' => $comment->id]);
    }

    public function test_bulk_actions_settings_and_blocklist_use_safe_metadata(): void
    {
        $admin = User::factory()->admin()->create();
        $post = $this->postForLocale('en');
        $first = $this->comment($post, ['author_email' => 'bulk-one@example.com', 'author_email_hash' => hash('sha256', 'bulk-one@example.com')]);
        $second = $this->comment($post, ['author_email' => 'bulk-two@example.com', 'author_email_hash' => hash('sha256', 'bulk-two@example.com')]);

        $this->actingAs($admin)
            ->post(route('admin.comment-moderation.bulk'), [
                'action' => 'approve',
                'comment_ids' => [$first->id, $second->id],
            ])
            ->assertRedirect();

        $this->assertSame(2, Comment::query()->whereIn('id', [$first->id, $second->id])->where('status', 'approved')->count());

        $this->actingAs($admin)
            ->post(route('admin.comment-moderation.block', $first), ['type' => 'email'])
            ->assertRedirect();

        $this->assertDatabaseHas('comment_blocklists', [
            'type' => 'email',
            'hash' => hash('sha256', 'bulk-one@example.com'),
        ]);
        $this->assertDatabaseMissing('comment_blocklists', ['label' => 'bulk-one@example.com']);

        $this->actingAs($admin)
            ->put(route('admin.comment-moderation.settings'), [
                'comments_active' => '1',
                'approval_required' => '1',
                'auto_close_days' => 14,
                'replies_active' => '1',
                'minimum_length' => 5,
                'maximum_length' => 800,
                'maximum_links' => 2,
                'blocked_words' => "casino\nloan offer",
            ])
            ->assertRedirect();

        $payload = SystemSetting::query()->where('group', 'comments')->firstOrFail()->payload;
        $this->assertSame(14, $payload['auto_close_days']);
        $this->assertSame(['casino', 'loan offer'], $payload['blocked_words']);

        $this->actingAs($admin)
            ->put(route('admin.comment-moderation.posts.settings', $post), [
                'comments_enabled' => '0',
                'comments_moderation_required' => '1',
            ])
            ->assertRedirect();

        $this->assertFalse($post->refresh()->comments_enabled);
    }

    public function test_comment_submission_route_is_rate_limited(): void
    {
        $middleware = Route::getRoutes()->getByName('comments.store')?->gatherMiddleware() ?? [];

        $this->assertContains('throttle:comments', $middleware);
    }

    public function test_comment_moderation_sources_avoid_forbidden_patterns(): void
    {
        $files = [
            app_path('Http/Controllers/CommentSubmissionController.php'),
            app_path('Http/Controllers/Admin/CommentModerationController.php'),
            app_path('Http/Requests/Comments/StoreCommentRequest.php'),
            app_path('Services/Comments/CommentStore.php'),
            app_path('Services/Comments/CommentModerationService.php'),
            app_path('Services/Comments/CommentSpamCheckService.php'),
            app_path('Services/Comments/CommentSettingsStore.php'),
            app_path('Services/Comments/CommentBlocklistService.php'),
            app_path('Actions/Comments/CommentReplyAction.php'),
            app_path('Actions/Comments/EditCommentAction.php'),
            app_path('Actions/Comments/BulkCommentAction.php'),
            resource_path('views/dashboard/comment-moderation/index.blade.php'),
            resource_path('views/components/comments/comment-card.blade.php'),
            resource_path('views/components/comments/action-bar.blade.php'),
            resource_path('views/components/comments/filter-bar.blade.php'),
            resource_path('views/components/comments/settings-panel.blade.php'),
            resource_path('views/components/comments/bulk-actions.blade.php'),
        ];

        foreach ($files as $file) {
            $contents = file_get_contents($file);
            $this->assertIsString($contents);
            $this->assertStringNotContainsString('Livewire', $contents, $file);
            $this->assertStringNotContainsString('livewire', $contents, $file);
            $this->assertStringNotContainsString('cdn.tailwindcss.com', $contents, $file);
            $this->assertStringNotContainsString('unpkg.com/alpine', $contents, $file);
            $this->assertStringNotContainsString('127.0.0.1', $contents, $file);
        }
    }

    /** @param array<string, mixed> $overrides */
    private function postForLocale(string $locale, array $overrides = []): BlogPost
    {
        $language = Locale::query()->where('locale', $locale)->firstOrFail();

        return BlogPost::factory()->create([
            'locale_id' => $language->id,
            'status' => 'published',
            'published_at' => now(),
            ...$overrides,
        ]);
    }

    /** @param array<string, mixed> $overrides */
    private function comment(BlogPost $post, array $overrides = []): Comment
    {
        return Comment::query()->create([
            'blog_post_id' => $post->id,
            'locale_id' => $post->locale_id,
            'author_name' => 'Reader',
            'author_email' => 'reader@example.com',
            'author_email_hash' => hash('sha256', 'reader@example.com'),
            'ip_hash' => hash('sha256', '127.0.0.1'),
            'user_agent_metadata' => ['hash' => hash('sha256', 'Feature Test'), 'length' => 12],
            'content' => '<p>Pending queue question</p>',
            'content_excerpt' => 'Pending queue question',
            'status' => 'pending',
            'spam_score' => 0,
            ...$overrides,
        ]);
    }
}
