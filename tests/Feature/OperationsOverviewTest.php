<?php

namespace Tests\Feature;

use App\Models\AbuseSignal;
use App\Models\Domain;
use App\Models\InboundMailConnection;
use App\Models\Locale;
use App\Models\Mailbox;
use App\Models\MailboxMessage;
use App\Models\Membership;
use App\Models\Plan;
use App\Models\SystemBackup;
use App\Models\SystemHealthCheck;
use App\Models\SystemNotification;
use App\Models\UpdateCheck;
use App\Models\User;
use App\Models\UserAuditEvent;
use App\Services\Dashboard\DashboardSummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class OperationsOverviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Cache::flush();
    }

    public function test_operations_overview_renders_cached_metrics_from_services(): void
    {
        $admin = User::factory()->admin()->create();
        $domain = Domain::query()->create([
            'domain_name' => 'mail.example.test',
            'display_name' => 'Mail Example',
            'is_active' => true,
            'is_public' => true,
            'catch_all_ready' => true,
            'status' => 'ready',
        ]);
        $mailbox = Mailbox::query()->create([
            'domain_id' => $domain->id,
            'address' => 'today@mail.example.test',
            'local_part' => 'today',
            'status' => 'active',
            'mailbox_type' => 'guest',
        ]);
        MailboxMessage::query()->create([
            'mailbox_id' => $mailbox->id,
            'sender_email' => 'sender@example.test',
            'subject' => 'Hello',
            'received_at' => now(),
        ]);
        $plan = Plan::query()->create(['key' => 'premium', 'name' => 'Premium', 'description' => 'Premium plan', 'is_active' => true]);
        Membership::query()->create(['user_id' => $admin->id, 'plan_id' => $plan->id, 'status' => 'active', 'starts_at' => now()]);
        Locale::query()->create([
            'language_name' => 'French',
            'native_name' => 'Français',
            'locale' => 'fr',
            'region' => 'Europe',
            'market_readiness' => 'needs_review',
            'launch_status' => 'draft',
        ]);
        SystemNotification::factory()->for($admin, 'recipient')->create(['event_key' => 'new_pending_comment', 'title' => 'New pending comment']);
        AbuseSignal::query()->create([
            'signal_type' => 'suspicious_comment',
            'severity' => 'medium',
            'source_module' => 'comments',
            'first_seen_at' => now(),
            'last_seen_at' => now(),
            'status' => 'open',
            'deduplication_key' => Str::random(40),
        ]);
        UserAuditEvent::query()->create(['actor_id' => $admin->id, 'event' => 'failed_admin_login', 'module' => 'trust', 'action' => 'Failed admin login', 'severity' => 'warning']);
        UserAuditEvent::query()->create(['actor_id' => $admin->id, 'event' => 'domain.updated', 'module' => 'mail-infrastructure', 'action' => 'Domain updated', 'severity' => 'info']);
        UpdateCheck::query()->create([
            'uuid' => (string) Str::uuid(),
            'channel' => 'stable',
            'current_version' => '1.0.0',
            'latest_version' => '1.1.0',
            'status' => 'update_available',
            'endpoint' => 'https://www.doic.net/update',
            'https_endpoint' => true,
            'checked_at' => now(),
        ]);
        SystemBackup::query()->create(['uuid' => (string) Str::uuid(), 'type' => 'database', 'status' => 'completed']);
        SystemHealthCheck::query()->create(['uuid' => (string) Str::uuid(), 'overall_status' => 'healthy', 'summary' => [], 'results' => [], 'checked_at' => now()]);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Operations Overview')
            ->assertSee('Active inboxes')
            ->assertSee('Mailboxes today')
            ->assertSee('Emails today')
            ->assertSee('Pending comments')
            ->assertSee('Abuse alerts')
            ->assertSee('Failed admin logins')
            ->assertSee('Update availability')
            ->assertSee('Active Premium')
            ->assertSee('Recent activity')
            ->assertSee('Domain updated')
            ->assertSee('Live')
            ->assertSee('30s interval');

        $this->assertTrue(Cache::has('dashboard.operations.summary.admin'));
    }

    public function test_sensitive_metrics_are_filtered_by_role_and_quick_actions_are_permission_aware(): void
    {
        $editor = User::factory()->editor()->create();

        $this->actingAs($editor)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Operations Overview')
            ->assertDontSee('Abuse alerts')
            ->assertDontSee('Failed admin logins')
            ->assertDontSee('Create backup')
            ->assertDontSee('Check for updates')
            ->assertDontSee('Review pending comments');

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Create backup')
            ->assertSee('Check for updates')
            ->assertSee('Open Mailbox Operations')
            ->assertSee('Review pending comments')
            ->assertSee('Review security alerts');
    }

    public function test_dashboard_summary_uses_short_cache_lifetime(): void
    {
        $admin = User::factory()->admin()->create();

        $first = app(DashboardSummaryService::class)->summary($admin);

        Domain::query()->create([
            'domain_name' => 'cache.example.test',
            'display_name' => 'Cache Example',
            'status' => 'ready',
        ]);
        Mailbox::query()->create([
            'domain_id' => Domain::query()->first()->id,
            'address' => 'cached@cache.example.test',
            'local_part' => 'cached',
            'status' => 'active',
        ]);

        $second = app(DashboardSummaryService::class)->summary($admin);

        $this->assertSame($first['last_updated']->timestamp, $second['last_updated']->timestamp);
        $this->assertSame(20, $second['cache_seconds']);
    }

    public function test_dashboard_sources_keep_queries_out_of_blade_and_avoid_forbidden_patterns(): void
    {
        $source = collect([
            resource_path('views/dashboard/operations-overview/index.blade.php'),
            resource_path('views/components/dashboard'),
        ])->flatMap(fn (string $path) => File::isDirectory($path) ? File::allFiles($path) : [$path])
            ->map(fn ($file): string => File::get((string) $file))
            ->implode("\n");

        $this->assertStringNotContainsString('::query', $source);
        $this->assertStringNotContainsString('App\\Models', $source);
        $this->assertStringNotContainsString('Livewire', $source);
        $this->assertStringNotContainsString('livewire', $source);
        $this->assertStringNotContainsString('cdn.tailwindcss', $source);
        $this->assertStringNotContainsString('127.0.0.1', $source);
        $this->assertStringNotContainsString('/build/assets', $source);
    }

    public function test_live_metrics_endpoint_is_authorized_cached_and_sanitized_by_role(): void
    {
        $admin = User::factory()->admin()->create();
        $editor = User::factory()->editor()->create();
        $member = User::factory()->create();

        AbuseSignal::query()->create([
            'signal_type' => 'suspicious_comment',
            'severity' => 'critical',
            'source_module' => 'comments',
            'first_seen_at' => now(),
            'last_seen_at' => now(),
            'status' => 'open',
            'deduplication_key' => Str::random(40),
        ]);
        UserAuditEvent::query()->create(['event' => 'failed_admin_login', 'module' => 'trust', 'action' => 'Failed admin login', 'severity' => 'warning']);

        $this->actingAs($member)->getJson(route('admin.dashboard.live-metrics'))->assertForbidden();

        $adminResponse = $this->actingAs($admin)->getJson(route('admin.dashboard.live-metrics'))
            ->assertOk()
            ->assertJsonStructure([
                'metrics' => [['key', 'label', 'value', 'detail', 'icon', 'tone']],
                'alerts' => [['key', 'title', 'message', 'severity', 'route', 'url']],
                'last_updated',
                'cache_seconds',
                'stale_after_seconds',
                'allowed_intervals',
                'default_interval',
            ])
            ->json();

        $this->assertContains('abuse_alerts', collect($adminResponse['metrics'])->pluck('key')->all());
        $this->assertContains('failed_admin_logins', collect($adminResponse['metrics'])->pluck('key')->all());
        $this->assertSame([15, 30, 60], $adminResponse['allowed_intervals']);
        $this->assertSame(30, $adminResponse['default_interval']);
        $this->assertSame(120, $adminResponse['stale_after_seconds']);
        $this->assertTrue(Cache::has('dashboard.operations.summary.admin'));

        $editorResponse = $this->actingAs($editor)->getJson(route('admin.dashboard.live-metrics'))
            ->assertOk()
            ->json();

        $this->assertNotContains('abuse_alerts', collect($editorResponse['metrics'])->pluck('key')->all());
        $this->assertNotContains('failed_admin_logins', collect($editorResponse['metrics'])->pluck('key')->all());
    }

    public function test_live_payload_deduplicates_critical_alerts_and_keeps_links_permission_aware(): void
    {
        $admin = User::factory()->admin()->create();
        $editor = User::factory()->editor()->create();
        $domain = Domain::query()->create([
            'domain_name' => 'offline.example.test',
            'display_name' => 'Offline Example',
            'status' => 'offline',
        ]);
        InboundMailConnection::query()->create([
            'domain_id' => $domain->id,
            'name' => 'Primary IMAP',
            'host' => 'imap.example.test',
            'username' => 'inbox@example.test',
            'encrypted_password' => 'secret',
            'status' => 'failed',
        ]);
        SystemBackup::query()->create(['uuid' => (string) Str::uuid(), 'type' => 'database', 'status' => 'failed']);
        SystemHealthCheck::query()->create(['uuid' => (string) Str::uuid(), 'overall_status' => 'critical', 'summary' => [], 'results' => [], 'checked_at' => now()]);
        UpdateCheck::query()->create([
            'uuid' => (string) Str::uuid(),
            'channel' => 'stable',
            'current_version' => '1.0.0',
            'status' => 'failed',
            'endpoint' => 'https://www.doic.net/update',
            'checked_at' => now(),
        ]);

        $payload = $this->actingAs($admin)->getJson(route('admin.dashboard.live-metrics'))->assertOk()->json();
        $keys = collect($payload['alerts'])->pluck('key')->all();

        $this->assertContains('domain_offline', $keys);
        $this->assertContains('mail_connection_failed', $keys);
        $this->assertContains('backup_failed', $keys);
        $this->assertContains('critical_system_health', $keys);
        $this->assertContains('update_failed', $keys);
        $this->assertCount(count(array_unique($keys)), $keys);
        $this->assertNotNull(collect($payload['alerts'])->firstWhere('key', 'domain_offline')['url']);

        $editorPayload = $this->actingAs($editor)->getJson(route('admin.dashboard.live-metrics'))->assertOk()->json();
        $this->assertNull(collect($editorPayload['alerts'])->firstWhere('key', 'domain_offline')['url'] ?? null);
    }

    public function test_operations_overview_renders_live_controls_and_keeps_initial_blade_fallback(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('dashboardLive', false)
            ->assertSee('live-metrics')
            ->assertSee('Auto-refresh on')
            ->assertSee('30s interval')
            ->assertSee('Refresh')
            ->assertSee('Active inboxes')
            ->assertSee('Data may be stale')
            ->assertSee('Connection unavailable');

        $script = File::get(resource_path('js/app.js'));

        $this->assertStringContainsString('fetch(this.endpoint', $script);
        $this->assertStringContainsString('visibilitychange', $script);
        $this->assertStringContainsString('dashboard-refresh-interval', $script);
        $this->assertStringContainsString('Dashboard refresh failed', $script);
        $this->assertStringContainsString('changedMetricKeys', $script);
    }
}
