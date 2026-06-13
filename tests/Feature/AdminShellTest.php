<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Admin\AdminCommandRegistry;
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
            ->assertSee('Operational metrics')
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

    public function test_command_registry_contains_required_commands_and_synonyms(): void
    {
        $commands = app(AdminCommandRegistry::class)->commands();
        $titles = array_column($commands, 'title');

        foreach ([
            'Go to Operations Overview',
            'Go to Mailbox Operations',
            'Go to Product Analytics',
            'Go to Locale Launch Center',
            'Go to Translation Center',
            'Go to Blog Studio',
            'Go to Page Studio',
            'Go to Sections Studio',
            'Go to Media Library',
            'Go to SEO Growth Center',
            'Go to Security Defense Center',
            'Go to Update Center',
            'Go to Backups & Health',
            'Create blog post',
            'Create page',
            'Check for updates',
            'Create backup',
        ] as $title) {
            $this->assertContains($title, $titles);
        }

        $keywordsByTitle = collect($commands)->mapWithKeys(fn (array $command): array => [
            $command['title'] => $command['keywords'],
        ]);

        $this->assertContains('language', $keywordsByTitle['Go to Locale Launch Center']);
        $this->assertContains('seo', $keywordsByTitle['Go to SEO Growth Center']);
        $this->assertContains('backup', $keywordsByTitle['Go to Backups & Health']);
        $this->assertContains('mail', $keywordsByTitle['Go to Mailbox Operations']);
        $this->assertContains('security', $keywordsByTitle['Go to Security Defense Center']);
        $this->assertContains('theme', $keywordsByTitle['Go to Theme Launch Center']);
        $this->assertContains('font', $keywordsByTitle['Go to Typography Center']);
        $this->assertContains('user', $keywordsByTitle['Go to People & Identity']);
    }

    public function test_commands_are_permission_aware_and_use_named_route_urls(): void
    {
        $registry = app(AdminCommandRegistry::class);
        $adminCommands = $registry->visibleFor(User::factory()->admin()->make());

        $this->assertNotEmpty($adminCommands);
        $this->assertSame([], $registry->visibleFor(User::factory()->make()));

        foreach ($adminCommands as $command) {
            $this->assertTrue(app('router')->has($command['route']), $command['route']);
            $this->assertSame(route($command['route']), $command['url']);
        }
    }

    public function test_command_palette_renders_accessible_dialog_and_mobile_trigger(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Open command palette')
            ->assertSee('role="dialog"', false)
            ->assertSee('aria-modal="true"', false)
            ->assertSee('Search commands')
            ->assertSee('Go to Locale Launch Center');
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
