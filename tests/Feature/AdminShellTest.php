<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminShellTest extends TestCase
{
    use RefreshDatabase;

    private string $recoveryPath;

    private ?string $originalRecovery = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->recoveryPath = storage_path('app/installer-recovery.flag');
        $this->originalRecovery = file_exists($this->recoveryPath)
            ? file_get_contents($this->recoveryPath)
            : null;

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

    public function test_unauthenticated_users_cannot_access_dashboard(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_normal_users_cannot_access_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertForbidden();
    }

    public function test_admin_dashboard_renders_inside_admin_shell(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Operations Overview')
            ->assertSee('Admin shell is ready')
            ->assertSee('Skip to main content')
            ->assertSee(route('logout'), false);
    }

    public function test_admin_layout_uses_vite_and_named_routes(): void
    {
        $layout = file_get_contents(resource_path('views/components/admin/layout.blade.php'));
        $sidebar = file_get_contents(resource_path('views/components/admin/sidebar.blade.php'));
        $header = file_get_contents(resource_path('views/components/admin/header.blade.php'));

        $this->assertStringContainsString('@vite', $layout);
        $this->assertStringContainsString("route('dashboard')", $sidebar);
        $this->assertStringContainsString("route('logout')", $header);
        $this->assertStringNotContainsString('/build/assets/', $layout.$sidebar.$header);
        $this->assertStringNotContainsString('127.0.0.1', $layout.$sidebar.$header);
    }
}
