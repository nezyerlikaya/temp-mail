<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\Mailbox;
use App\Models\MailboxMessage;
use App\Models\User;
use App\Models\UserAuditEvent;
use App\Services\Mailboxes\MailboxMessageService;
use App\Services\Mailboxes\MessageSanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MailboxAdministrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_message_html_is_sanitized_and_remote_resources_are_blocked(): void
    {
        $html = '<p onclick="steal()">Hello</p><script>alert(1)</script><img src="https://tracker.example/pixel"><a href="javascript:alert(2)">bad</a><img src="data:image/png;base64,AAAA">';
        $clean = app(MessageSanitizer::class)->sanitize($html);

        $this->assertStringContainsString('<p>Hello</p>', $clean);
        $this->assertStringNotContainsString('script', $clean);
        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringNotContainsString('tracker.example', $clean);
        $this->assertStringNotContainsString('javascript:', $clean);
        $this->assertStringContainsString('data:image/png;base64,AAAA', $clean);
    }

    public function test_mailbox_detail_lists_safe_message_metadata_and_privileged_admin_can_open_content(): void
    {
        [$admin, $mailbox] = $this->mailbox();
        $message = $this->message($mailbox, ['subject' => 'Account verification', 'preview_text' => 'Safe preview', 'plain_text_body' => 'PRIVATE BODY']);

        $this->actingAs($admin)->get(route('admin.mailbox-operations.show', $mailbox))
            ->assertOk()->assertSee('Inbox messages')->assertSee('Account verification')->assertSee('Safe preview')->assertDontSee('PRIVATE BODY');

        $this->actingAs($admin)->get(route('admin.mailbox-operations.messages.show', [$mailbox, $message]))
            ->assertOk()->assertSee('Private mailbox content')->assertSee('PRIVATE BODY')->assertSee('sandbox=""', false);

        $audit = UserAuditEvent::query()->where('event', 'mailbox.message_accessed')->firstOrFail();
        $encoded = json_encode($audit->metadata);
        $this->assertStringNotContainsString('PRIVATE BODY', $encoded);
        $this->assertStringNotContainsString('Account verification', $encoded);
    }

    public function test_moderator_can_list_metadata_but_cannot_view_message_content_or_manage_state(): void
    {
        [$admin, $mailbox] = $this->mailbox();
        $moderator = User::factory()->create(['role' => 'moderator', 'is_admin' => true]);
        $message = $this->message($mailbox);

        $this->actingAs($moderator)->get(route('admin.mailbox-operations.show', $mailbox))
            ->assertOk()->assertSee('Content access restricted');
        $this->actingAs($moderator)->get(route('admin.mailbox-operations.messages.show', [$mailbox, $message]))->assertForbidden();
        $this->actingAs($moderator)->post(route('admin.mailbox-operations.messages.action', [$mailbox, $message]), ['action' => 'read'])->assertForbidden();
    }

    public function test_lock_unlock_and_expire_actions_enforce_state_and_record_timeline(): void
    {
        [$admin, $mailbox] = $this->mailbox();

        $this->actingAs($admin)->post(route('admin.mailbox-operations.lock', $mailbox))->assertRedirect();
        $this->assertSame('locked', $mailbox->refresh()->status);

        $this->actingAs($admin)->post(route('admin.mailbox-operations.unlock', $mailbox))->assertRedirect();
        $this->assertSame('active', $mailbox->refresh()->status);

        $this->actingAs($admin)->post(route('admin.mailbox-operations.expire', $mailbox), ['confirmation' => 'EXPIRE'])->assertRedirect();
        $mailbox->refresh();
        $this->assertSame('expired', $mailbox->status);
        $this->assertNotNull($mailbox->expires_at);
        $this->assertSame(['mailbox_created', 'mailbox_locked', 'mailbox_unlocked', 'mailbox_expired'], collect($mailbox->activity_timeline)->pluck('event')->all());
        $this->assertDatabaseHas('user_audit_events', ['event' => 'mailbox.expired', 'actor_id' => $admin->id]);
    }

    public function test_expire_and_empty_require_confirmation(): void
    {
        [$admin, $mailbox] = $this->mailbox();
        $this->message($mailbox);

        $this->actingAs($admin)->post(route('admin.mailbox-operations.expire', $mailbox))->assertSessionHasErrors('confirmation');
        $this->actingAs($admin)->post(route('admin.mailbox-operations.empty', $mailbox))->assertSessionHasErrors('confirmation');
        $this->assertSame('active', $mailbox->refresh()->status);
        $this->assertSame(1, $mailbox->messages()->whereNull('deleted_at')->count());
    }

    public function test_empty_inbox_and_message_actions_update_counts_without_auditing_content(): void
    {
        [$admin, $mailbox] = $this->mailbox();
        $message = $this->message($mailbox, ['plain_text_body' => 'DO NOT LOG THIS BODY']);

        $this->actingAs($admin)->post(route('admin.mailbox-operations.messages.action', [$mailbox, $message]), ['action' => 'read'])->assertRedirect();
        $this->assertNotNull($message->refresh()->read_at);

        $this->actingAs($admin)->post(route('admin.mailbox-operations.messages.action', [$mailbox, $message]), ['action' => 'unread'])->assertRedirect();
        $this->assertNull($message->refresh()->read_at);

        $this->actingAs($admin)->post(route('admin.mailbox-operations.empty', $mailbox), ['confirmation' => 'EMPTY'])->assertRedirect();
        $this->assertSame(0, $mailbox->refresh()->message_count);
        $this->assertNotNull($message->refresh()->deleted_at);
        $this->assertStringNotContainsString('DO NOT LOG THIS BODY', UserAuditEvent::query()->get()->toJson());
    }

    public function test_delete_message_requires_confirmation_and_keeps_attachments_unavailable(): void
    {
        [$admin, $mailbox] = $this->mailbox();
        $message = $this->message($mailbox, ['attachment_count' => 2]);

        $this->actingAs($admin)->post(route('admin.mailbox-operations.messages.action', [$mailbox, $message]), ['action' => 'delete'])
            ->assertSessionHasErrors('confirmation');
        $this->assertNull($message->refresh()->deleted_at);

        $this->actingAs($admin)->post(route('admin.mailbox-operations.messages.action', [$mailbox, $message]), ['action' => 'delete', 'confirmation' => 'DELETE'])
            ->assertRedirect(route('admin.mailbox-operations.show', $mailbox));
        $this->assertNotNull($message->refresh()->deleted_at);
        $this->assertFalse(collect(app('router')->getRoutes())->contains(fn ($route): bool => str_contains((string) $route->getName(), 'attachment')));
    }

    /** @return array{User, Mailbox} */
    private function mailbox(): array
    {
        $admin = User::factory()->admin()->create();
        $domain = Domain::query()->create([
            'domain_name' => 'messages.example', 'display_name' => 'Messages Example', 'status' => 'ready', 'is_active' => true, 'is_public' => true,
            'created_by' => $admin->id, 'updated_by' => $admin->id,
        ]);
        $mailbox = Mailbox::query()->create([
            'domain_id' => $domain->id, 'address' => 'inbox@messages.example', 'local_part' => 'inbox',
            'mailbox_type' => 'guest', 'status' => 'active', 'last_activity_at' => now(), 'message_count' => 0,
            'activity_timeline' => [['event' => 'mailbox_created', 'label' => 'Mailbox created', 'detail' => 'Lifecycle started.', 'occurred_at' => now()->toIso8601String()]],
        ]);

        return [$admin, $mailbox];
    }

    /** @param array<string, mixed> $overrides */
    private function message(Mailbox $mailbox, array $overrides = []): MailboxMessage
    {
        return app(MailboxMessageService::class)->store($mailbox, [
            'sender_email' => 'sender@example.test', 'sender_name' => 'Sender', 'subject' => 'Test message',
            'preview_text' => 'Safe preview', 'plain_text_body' => 'Private body',
            'html_body' => '<p>Private body</p>', 'raw_headers' => ['Message-ID' => '<safe@example.test>'],
            'attachment_count' => 0, 'message_size' => 2048, 'received_at' => now(), ...$overrides,
        ]);
    }
}
