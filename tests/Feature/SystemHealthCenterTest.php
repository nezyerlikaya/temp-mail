<?php

namespace Tests\Feature;

use App\Models\SystemHealthCheck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SystemHealthCenterTest extends TestCase
{
    use RefreshDatabase;

    private string $recoveryPath;

    private ?string $originalRecovery = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->recoveryPath = storage_path('app/installer-recovery.flag');
        $this->originalRecovery = file_exists($this->recoveryPath) ? file_get_contents($this->recoveryPath) : null;

        if (file_exists($this->recoveryPath)) {
            unlink($this->recoveryPath);
        }
    }

    protected function tearDown(): void
    {
        if ($this->originalRecovery !== null) {
            file_put_contents($this->recoveryPath, $this->originalRecovery);
        } elseif (file_exists($this->recoveryPath)) {
            unlink($this->recoveryPath);
        }

        parent::tearDown();
    }

    public function test_system_health_renders_inside_backups_health_admin_shell(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->get(route('admin.backups-health.index'))
            ->assertOk()
            ->assertSee('Operations Health Center')
            ->assertSee('Health checks')
            ->assertSee('PHP version')
            ->assertSee('Database connection')
            ->assertSee('Run Health Check')
            ->assertDontSee('The route, authorization boundary');
    }

    public function test_manual_run_health_check_validates_permissions_and_records_history(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create();

        $this->actingAs($member)
            ->post(route('admin.backups-health.health-check.run'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('admin.backups-health.health-check.run'))
            ->assertRedirect(route('admin.backups-health.index'));

        $this->assertDatabaseHas('system_health_checks', ['checked_by' => $admin->id]);
        $this->assertDatabaseHas('user_audit_events', [
            'actor_id' => $admin->id,
            'event' => 'system.health_check_run',
        ]);
    }

    public function test_health_checks_do_not_expose_secret_values(): void
    {
        config([
            'app.key' => 'secret-app-key-value-should-hide',
            'mail.mailers.smtp.password' => 'smtp-secret-value-should-hide',
        ]);

        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->get(route('admin.backups-health.index'))
            ->assertOk()
            ->assertSee('APP_KEY is configured.')
            ->assertSee('Secret value is intentionally hidden.')
            ->assertDontSee('secret-app-key-value-should-hide')
            ->assertDontSee('smtp-secret-value-should-hide');
    }

    public function test_health_status_summary_updates_after_manual_run(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->post(route('admin.backups-health.health-check.run'))
            ->assertRedirect(route('admin.backups-health.index'));

        $record = SystemHealthCheck::query()->firstOrFail();

        $this->actingAs($owner)
            ->get(route('admin.backups-health.index'))
            ->assertOk()
            ->assertSee($record->checked_at->format('M j, Y H:i'))
            ->assertSee('Healthy')
            ->assertSee('Needs attention')
            ->assertSee('Critical');
    }

    public function test_health_history_is_pruned_to_a_bounded_size(): void
    {
        $owner = User::factory()->owner()->create();

        for ($i = 0; $i < 55; $i++) {
            SystemHealthCheck::query()->create([
                'uuid' => fake()->uuid(),
                'overall_status' => 'healthy',
                'summary' => ['healthy' => 1, 'attention' => 0, 'critical' => 0],
                'results' => [],
                'checked_by' => $owner->id,
                'checked_at' => now()->subMinutes(60 - $i),
            ]);
        }

        $this->actingAs($owner)
            ->post(route('admin.backups-health.health-check.run'))
            ->assertRedirect(route('admin.backups-health.index'));

        $this->assertLessThanOrEqual(50, SystemHealthCheck::query()->count());
    }

    public function test_critical_issue_records_notification_readiness(): void
    {
        $owner = User::factory()->owner()->create();
        $lock = storage_path('app/installed.lock');
        $originalLock = is_file($lock) ? File::get($lock) : null;

        if (is_file($lock)) {
            File::delete($lock);
        }

        try {
            $this->actingAs($owner)
                ->post(route('admin.backups-health.health-check.run'))
                ->assertRedirect(route('admin.backups-health.index'));

            $record = SystemHealthCheck::query()->latest()->firstOrFail();

            $this->assertSame('critical', $record->overall_status);
            $this->assertDatabaseHas('user_audit_events', [
                'actor_id' => $owner->id,
                'event' => 'system.health_check_run',
            ]);
            $this->assertTrue(collect($record->results)->contains(fn (array $result): bool => $result['id'] === 'installed-lock' && $result['status'] === 'critical'));
        } finally {
            if ($originalLock !== null) {
                File::put($lock, $originalLock);
            }
        }
    }
}
