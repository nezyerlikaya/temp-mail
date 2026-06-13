<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Comment;
use App\Models\Locale;
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
            resource_path('views/dashboard/comment-moderation/index.blade.php'),
            resource_path('views/components/comments/comment-card.blade.php'),
            resource_path('views/components/comments/action-bar.blade.php'),
            resource_path('views/components/comments/filter-bar.blade.php'),
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
