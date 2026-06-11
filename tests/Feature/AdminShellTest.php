<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Admin\AdminNavigationRegistry;
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
            ->assertSee('Mailbox Operations')
            ->assertSee('Settings')
            ->assertSee(route('logout'), false);
    }

    public function test_navigation_registry_matches_blueprint_menu_map(): void
    {
        $groups = app(AdminNavigationRegistry::class)->groups();

        $this->assertSame([
            'Workspace',
            'Markets',
            'Content',
            'Mail Infrastructure',
            'Growth',
            'People',
            'Brand',
            'Trust',
            'System',
        ], array_column($groups, 'label'));

        $this->assertSame([
            'Operations Overview',
            'Mailbox Operations',
            'Product Analytics',
            'Locale Launch Center',
            'Translation Center',
            'Page Studio',
            'Blog Studio',
            'Taxonomy',
            'Sections Studio',
            'Media Library',
            'Comment Moderation',
            'SEO Growth Center',
            'Domains',
            'IMAP/SMTP',
            'Mailbox Rules',
            'Blocked Lists',
            'Plans & Memberships',
            'API Access',
            'Integrations',
            'People & Identity',
            'Roles & Permissions',
            'Author Profiles',
            'Theme Launch Center',
            'Appearance Studio',
            'Typography Center',
            'Security Defense Center',
            'Abuse Reports',
            'Activity & Audit Logs',
            'Update Center',
            'Notifications',
            'Email Templates',
            'Backups & Health',
            'Settings',
        ], collect($groups)->flatMap(fn (array $group): array => array_column($group['items'], 'label'))->all());
    }

    public function test_navigation_is_permission_aware(): void
    {
        $registry = app(AdminNavigationRegistry::class);

        $this->assertCount(9, $registry->visibleFor(User::factory()->admin()->make(), 'dashboard'));
        $this->assertSame([], $registry->visibleFor(User::factory()->make(), 'dashboard'));
    }

    public function test_placeholder_route_renders_and_marks_active_navigation(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.blog-studio.index'))
            ->assertOk()
            ->assertSee('Blog Studio')
            ->assertSee('aria-current="page"', false);
    }

    public function test_every_navigation_item_uses_an_existing_named_route(): void
    {
        foreach (app(AdminNavigationRegistry::class)->groups() as $group) {
            foreach ($group['items'] as $item) {
                $this->assertTrue(app('router')->has($item['route']), $item['route']);
            }
        }
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
