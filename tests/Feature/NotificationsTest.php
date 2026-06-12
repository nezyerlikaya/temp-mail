<?php

namespace Tests\Feature;

use App\Models\SystemNotification;
use App\Models\User;
use App\Services\Notifications\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_notifications_center_renders_inside_admin_shell(): void
    {
        $admin = User::factory()->admin()->create();
        SystemNotification::factory()->for($admin, 'recipient')->critical()->create();

        $this->actingAs($admin)
            ->get(route('admin.notifications.index'))
            ->assertOk()
            ->assertSee('Notifications')
            ->assertSee('Operational inbox')
            ->assertSee('Failed admin login')
            ->assertSee('Mark all read')
            ->assertDontSee('This workspace is ready for implementation.');
    }

    public function test_mark_read_mark_all_and_archive_actions_update_state(): void
    {
        $admin = User::factory()->admin()->create();
        $first = SystemNotification::factory()->for($admin, 'recipient')->create();
        $second = SystemNotification::factory()->for($admin, 'recipient')->create(['title' => 'Backup failed', 'event_key' => 'backup_failed']);

        $this->actingAs($admin)
            ->post(route('admin.notifications.mark-read', $first))
            ->assertRedirect();

        $this->assertNotNull($first->refresh()->read_at);

        $this->actingAs($admin)
            ->post(route('admin.notifications.mark-all-read'))
            ->assertRedirect();

        $this->assertNotNull($second->refresh()->read_at);

        $this->actingAs($admin)
            ->post(route('admin.notifications.archive', $second))
            ->assertRedirect(route('admin.notifications.index'));

        $this->assertNotNull($second->refresh()->archived_at);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'notification.archived', 'actor_id' => $admin->id]);
    }

    public function test_normal_users_cannot_view_admin_notifications(): void
    {
        $member = User::factory()->create();
        $notification = SystemNotification::factory()->for($member, 'recipient')->create();

        $this->actingAs($member)
            ->get(route('admin.notifications.index'))
            ->assertForbidden();

        $this->actingAs($member)
            ->get(route('admin.notifications.show', $notification))
            ->assertForbidden();
    }

    public function test_moderators_only_see_notifications_for_permitted_modules(): void
    {
        $moderator = User::factory()->create(['role' => 'moderator', 'is_admin' => true]);

        SystemNotification::factory()->for($moderator, 'recipient')->create([
            'title' => 'New pending comment',
            'event_key' => 'new_pending_comment',
            'related_module' => 'content',
        ]);
        SystemNotification::factory()->for($moderator, 'recipient')->create([
            'title' => 'Update available',
            'event_key' => 'update_available',
            'related_module' => 'system',
        ]);

        $this->actingAs($moderator)
            ->get(route('admin.notifications.index'))
            ->assertOk()
            ->assertSee('New pending comment')
            ->assertDontSee('Update available');
    }

    public function test_action_links_are_permission_aware(): void
    {
        $moderator = User::factory()->create(['role' => 'moderator', 'is_admin' => true]);
        $notification = SystemNotification::factory()->for($moderator, 'recipient')->create([
            'title' => 'Content review routed elsewhere',
            'related_module' => 'content',
            'action_route' => 'admin.page-studio.index',
        ]);

        $this->actingAs($moderator)
            ->get(route('admin.notifications.show', $notification))
            ->assertOk()
            ->assertSee('Action unavailable')
            ->assertDontSee(route('admin.page-studio.index'));
    }

    public function test_email_delivery_failure_does_not_break_in_app_notifications(): void
    {
        config(['mail.default' => '', 'mail.from.address' => '', 'mail.mailers.smtp.password' => 'hidden-provider-secret']);

        $admin = User::factory()->admin()->create();

        $created = app(NotificationService::class)->dispatch([
            'event_key' => 'backup_failed',
            'type' => 'system',
            'severity' => 'critical',
            'title' => 'Backup failed',
            'message' => 'The scheduled backup failed.',
            'related_module' => 'system',
            'action_route' => 'admin.backups-health.index',
        ], [$admin]);

        $this->assertCount(1, $created);
        $notification = $created->first()->refresh();

        $this->assertDatabaseHas('system_notifications', [
            'id' => $notification->id,
            'recipient_user_id' => $admin->id,
            'event_key' => 'backup_failed',
        ]);
        $this->assertContains($notification->email_status, ['skipped', 'failed']);

        $this->actingAs($admin)
            ->get(route('admin.notifications.index'))
            ->assertOk()
            ->assertSee('Backup failed')
            ->assertDontSee('hidden-provider-secret');
    }

    public function test_critical_security_notifications_resolve_to_owner_and_admin(): void
    {
        $owner = User::factory()->owner()->create();
        $admin = User::factory()->admin()->create();
        $editor = User::factory()->editor()->create();

        $created = app(NotificationService::class)->dispatch([
            'event_key' => 'failed_admin_login',
            'type' => 'security',
            'severity' => 'critical',
            'title' => 'Failed admin login',
            'message' => 'An admin login attempt failed.',
            'related_module' => 'trust',
            'action_route' => 'admin.security-defense-center.index',
        ], sendEmail: false);

        $this->assertTrue($created->pluck('recipient_user_id')->contains($owner->id));
        $this->assertTrue($created->pluck('recipient_user_id')->contains($admin->id));
        $this->assertFalse($created->pluck('recipient_user_id')->contains($editor->id));
    }
}
