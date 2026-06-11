<?php

namespace Tests\Feature;

use App\Models\UpdateCheck;
use App\Models\User;
use App\Services\Updates\UpdateCompatibilityChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class UpdateCenterTest extends TestCase
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

    public function test_update_center_renders_inside_admin_shell_and_replaces_placeholder(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->get(route('admin.update-center.index'))
            ->assertOk()
            ->assertSee('Update Center')
            ->assertSee('Check for updates')
            ->assertSee('Compatibility checklist')
            ->assertSee('Operations workspace')
            ->assertDontSee('The route, authorization boundary');
    }

    public function test_check_updates_uses_configured_https_endpoint_and_records_history(): void
    {
        config([
            'updates.endpoint' => 'https://updates.example.test/manifest',
            'updates.current_version' => '1.0.0',
        ]);

        Http::fake([
            'https://updates.example.test/manifest*' => Http::response([
                'version' => '1.2.0',
                'channel' => 'stable',
                'minimum_php' => '8.0.0',
                'minimum_laravel' => '10.0.0',
                'required_extensions' => ['pdo', 'json'],
                'signed' => true,
                'checksum' => hash('sha256', 'package'),
                'signature' => 'signed-manifest-token',
                'release_notes' => 'A safer production update.',
                'changelog' => [
                    ['severity' => 'security', 'message' => 'Hardens update checks.'],
                    ['severity' => 'fix', 'message' => 'Improves diagnostics.'],
                ],
            ], 200),
        ]);

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.update-center.check'), ['channel' => 'stable'])
            ->assertRedirect(route('admin.update-center.index'));

        Http::assertSent(fn ($request): bool => str_starts_with((string) $request->url(), 'https://updates.example.test/manifest')
            && $request['channel'] === 'stable'
            && $request['version'] === '1.0.0');

        $this->assertDatabaseHas('update_checks', [
            'channel' => 'stable',
            'current_version' => '1.0.0',
            'latest_version' => '1.2.0',
            'status' => 'available',
            'https_endpoint' => true,
            'signed_manifest' => true,
        ]);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'update.checked', 'actor_id' => $admin->id]);
    }

    public function test_unreachable_update_server_produces_clean_error(): void
    {
        config(['updates.endpoint' => 'https://updates.example.test/manifest']);

        Http::fake(fn () => throw new ConnectionException('network down'));

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->followingRedirects()
            ->post(route('admin.update-center.check'), ['channel' => 'stable'])
            ->assertOk()
            ->assertSee('The update check could not be completed')
            ->assertSee('The update server could not be reached');

        $this->assertDatabaseHas('update_checks', [
            'status' => 'failed',
            'https_endpoint' => true,
            'signed_manifest' => false,
        ]);
    }

    public function test_non_https_update_endpoint_is_rejected_before_request(): void
    {
        config(['updates.endpoint' => 'http://updates.example.test/manifest']);

        Http::fake();

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.update-center.check'), ['channel' => 'stable'])
            ->assertRedirect(route('admin.update-center.index'));

        Http::assertNothingSent();

        $this->assertDatabaseHas('update_checks', [
            'status' => 'failed',
            'https_endpoint' => false,
            'error_message' => 'The update server must use HTTPS before update checks can run.',
        ]);
    }

    public function test_validation_errors_render_accessible_summary(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->followingRedirects()
            ->from(route('admin.update-center.index'))
            ->post(route('admin.update-center.check'), ['channel' => 'nightly'])
            ->assertOk()
            ->assertSee('Please fix the highlighted fields.')
            ->assertSee('aria-invalid="true"', false)
            ->assertSee('role="alert"', false);
    }

    public function test_normal_user_cannot_access_or_check_updates(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.update-center.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.update-center.check'), ['channel' => 'stable'])
            ->assertForbidden();
    }

    public function test_compatibility_flags_missing_extension_and_bad_php(): void
    {
        $compatibility = app(UpdateCompatibilityChecker::class)->check([
            'minimum_php' => '99.0.0',
            'minimum_laravel' => '99.0.0',
            'required_extensions' => ['definitely_missing_extension_for_updates'],
        ]);

        $this->assertFalse($compatibility['compatible']);
        $this->assertTrue(collect($compatibility['results'])->contains(fn (array $check): bool => $check['status'] === 'failed'));
    }

    public function test_no_install_routes_or_actions_exist_in_part_one(): void
    {
        $updateRoutes = collect(Route::getRoutes())
            ->map(fn ($route): ?string => $route->getName())
            ->filter(fn (?string $name): bool => is_string($name) && str_contains($name, 'update-center'));

        $this->assertTrue($updateRoutes->contains('admin.update-center.index'));
        $this->assertTrue($updateRoutes->contains('admin.update-center.check'));
        $this->assertFalse($updateRoutes->contains(fn (string $name): bool => str_contains($name, 'install')));
        $this->assertFalse(File::exists(app_path('Actions/Updates/InstallUpdateAction.php')));
        $this->assertSame(0, UpdateCheck::query()->count());
    }
}
