<?php

namespace Tests\Feature;

use App\Models\NotificationRule;
use App\Models\SystemNotification;
use App\Models\User;
use App\Services\Notifications\NotificationRuleStore;
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
            'title' => 'Private system update notice',
            'event_key' => 'update_available',
            'related_module' => 'system',
        ]);

        $this->actingAs($moderator)
            ->get(route('admin.notifications.index'))
            ->assertOk()
            ->assertSee('New pending comment')
            ->assertDontSee('Private system update notice');
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

    public function test_notification_rules_save_with_owner_admin_permission_and_audit(): void
    {
        $admin = User::factory()->admin()->create();
        $editor = User::factory()->editor()->create();
        $payload = $this->rulesPayload([
            'domain_health_failed' => [
                'email_enabled' => '0',
                'digest_mode' => 'daily',
                'recipient_roles' => ['admin'],
            ],
        ]);

        $this->actingAs($editor)
            ->put(route('admin.notifications.rules.update'), ['rules' => $payload])
            ->assertForbidden();

        $this->actingAs($admin)
            ->put(route('admin.notifications.rules.update'), ['rules' => $payload])
            ->assertRedirect();

        $this->assertDatabaseHas('notification_rules', [
            'event_key' => 'domain_health_failed',
            'email_enabled' => false,
            'digest_mode' => 'daily',
        ]);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'notification_rule.updated', 'actor_id' => $admin->id]);
    }

    public function test_critical_events_bypass_digest_quiet_hours_and_inactive_rules(): void
    {
        $admin = User::factory()->admin()->create();
        app(NotificationRuleStore::class)->ensureDefaults();

        NotificationRule::query()->where('event_key', 'failed_admin_login')->update([
            'in_app_enabled' => false,
            'digest_mode' => 'daily',
            'quiet_hours_enabled' => true,
            'is_active' => false,
        ]);

        $created = app(NotificationService::class)->dispatch([
            'event_key' => 'failed_admin_login',
            'type' => 'security',
            'severity' => 'critical',
            'title' => 'Failed admin login',
            'message' => 'An admin login attempt failed.',
            'related_module' => 'trust',
            'action_route' => 'admin.security-defense-center.index',
        ], [$admin], false);

        $this->assertCount(1, $created);
        $notification = $created->first()->refresh();
        $this->assertSame('critical', $notification->severity);
        $this->assertNull($notification->digest_status);
    }

    public function test_deduplication_groups_repeated_events_and_preserves_window(): void
    {
        $admin = User::factory()->admin()->create();

        app(NotificationService::class)->dispatch($this->domainFailurePayload(), [$admin], false);
        app(NotificationService::class)->dispatch($this->domainFailurePayload(['message' => 'Domain health failed again.']), [$admin], false);

        $this->assertSame(1, SystemNotification::query()->where('recipient_user_id', $admin->id)->where('event_key', 'domain_health_failed')->count());

        $notification = SystemNotification::query()->where('recipient_user_id', $admin->id)->where('event_key', 'domain_health_failed')->firstOrFail();

        $this->assertSame(2, $notification->occurrence_count);
        $this->assertNotNull($notification->first_occurred_at);
        $this->assertNotNull($notification->last_occurred_at);
        $this->assertSame('pending', $notification->digest_status);
    }

    public function test_snooze_hides_notification_from_open_feed_until_expiry(): void
    {
        $admin = User::factory()->admin()->create();
        $notification = SystemNotification::factory()->for($admin, 'recipient')->create([
            'title' => 'Noisy domain health warning',
            'event_key' => 'domain_health_failed',
            'related_module' => 'mail-infrastructure',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.notifications.snooze', $notification), ['duration' => '1_hour'])
            ->assertRedirect();

        $this->assertNotNull($notification->refresh()->snoozed_until);

        $this->actingAs($admin)
            ->get(route('admin.notifications.index'))
            ->assertOk()
            ->assertDontSee('Noisy domain health warning');

        $this->assertDatabaseHas('user_audit_events', ['event' => 'notification.snoozed', 'actor_id' => $admin->id]);
    }

    public function test_rules_screen_shows_dependency_and_digest_readiness(): void
    {
        config(['mail.default' => '', 'mail.from.address' => '']);

        $admin = User::factory()->admin()->create();
        app(NotificationRuleStore::class)->ensureDefaults();

        NotificationRule::query()->where('event_key', 'domain_health_failed')->update(['email_enabled' => true]);

        $this->actingAs($admin)
            ->get(route('admin.notifications.index'))
            ->assertOk()
            ->assertSee('Notification rules')
            ->assertSee('Digest readiness')
            ->assertSee('Email channel is enabled, but mail delivery is not fully configured.');
    }

    /** @param array<string, array<string, mixed>> $overrides */
    private function rulesPayload(array $overrides = []): array
    {
        return app(NotificationRuleStore::class)->all()
            ->values()
            ->map(function (NotificationRule $rule) use ($overrides): array {
                $base = [
                    'event_key' => $rule->event_key,
                    'in_app_enabled' => $rule->in_app_enabled ? '1' : '0',
                    'email_enabled' => $rule->email_enabled ? '1' : '0',
                    'recipient_roles' => $rule->recipient_roles ?? [],
                    'digest_mode' => $rule->digest_mode,
                    'quiet_hours_enabled' => $rule->quiet_hours_enabled ? '1' : '0',
                    'quiet_hours_start' => $rule->quiet_hours_start,
                    'quiet_hours_end' => $rule->quiet_hours_end,
                    'is_active' => $rule->is_active ? '1' : '0',
                ];

                return [...$base, ...($overrides[$rule->event_key] ?? [])];
            })->all();
    }

    /** @param array<string, mixed> $overrides */
    private function domainFailurePayload(array $overrides = []): array
    {
        return [
            'event_key' => 'domain_health_failed',
            'type' => 'infrastructure',
            'severity' => 'warning',
            'title' => 'Domain health failed',
            'message' => 'Domain health failed.',
            'related_module' => 'mail-infrastructure',
            'action_route' => 'admin.domains.index',
            ...$overrides,
        ];
    }
}
