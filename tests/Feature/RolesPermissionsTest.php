<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Events\UserRoleChanged;
use App\Models\User;
use App\Services\Admin\AdminCommandRegistry;
use App\Services\Admin\AdminNavigationRegistry;
use App\Services\Users\AdminProtectionGuard;
use App\Services\Users\RolePermissionResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RolesPermissionsTest extends TestCase
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

    public function test_owner_can_view_roles_and_permission_matrix(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->get(route('admin.roles-permissions.index'))
            ->assertOk()
            ->assertSee('Roles & Permissions')
            ->assertSee('Permission matrix')
            ->assertSee('premium membership', false)
            ->assertSee('Critical access is protected');
    }

    public function test_role_summary_counts_match_the_permission_map(): void
    {
        $summaries = collect(app(RolePermissionResolver::class)->roleSummaries())
            ->keyBy(fn (array $summary): string => $summary['role']->value);

        $this->assertGreaterThan(0, $summaries['owner']['permissions']);
        $this->assertGreaterThan(0, $summaries['editor']['permissions']);
        $this->assertSame(0, $summaries['member']['permissions']);
    }

    public function test_member_cannot_access_dashboard_or_role_management(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)->get(route('dashboard'))->assertForbidden();
        $this->actingAs($member)->get(route('admin.roles-permissions.index'))->assertForbidden();
    }

    public function test_editor_sidebar_and_commands_only_include_allowed_modules(): void
    {
        $editor = User::factory()->editor()->make();
        $navigation = collect(app(AdminNavigationRegistry::class)->visibleFor($editor, 'dashboard'))
            ->flatMap(fn (array $group): array => array_column($group['items'], 'label'));
        $commands = collect(app(AdminCommandRegistry::class)->visibleFor($editor))->pluck('title');

        $this->assertTrue($navigation->contains('Blog Studio'));
        $this->assertFalse($navigation->contains('Roles & Permissions'));
        $this->assertFalse($navigation->contains('Settings'));
        $this->assertTrue($commands->contains('Go to Blog Studio'));
        $this->assertFalse($commands->contains('Go to Update Center'));
    }

    public function test_editor_can_access_dashboard_but_not_system_settings(): void
    {
        $editor = User::factory()->editor()->create();

        $this->actingAs($editor)->get(route('dashboard'))->assertOk();
        $this->actingAs($editor)->get(route('admin.settings.index'))->assertForbidden();
    }

    public function test_owner_can_assign_role_and_change_is_audited(): void
    {
        Event::fake([UserRoleChanged::class]);

        $owner = User::factory()->owner()->create();
        $member = User::factory()->create();

        $this->actingAs($owner)
            ->patch(route('admin.roles-permissions.update', $member), [
                'role' => 'editor',
                'confirm_critical_change' => '1',
            ])
            ->assertRedirect(route('admin.roles-permissions.index'));

        $this->assertSame('editor', $member->refresh()->role);
        $this->assertTrue($member->is_admin);
        $this->assertDatabaseHas('user_audit_events', [
            'actor_id' => $owner->id,
            'subject_id' => $member->id,
            'event' => 'user.role_changed',
        ]);
        Event::assertDispatched(UserRoleChanged::class);
    }

    public function test_role_update_requires_explicit_confirmation(): void
    {
        $owner = User::factory()->owner()->create();
        $member = User::factory()->create();

        $this->actingAs($owner)
            ->from(route('admin.roles-permissions.index'))
            ->patch(route('admin.roles-permissions.update', $member), ['role' => 'admin'])
            ->assertRedirect(route('admin.roles-permissions.index'))
            ->assertSessionHasErrors('confirm_critical_change');

        $this->assertSame('member', $member->refresh()->role);
    }

    public function test_admin_cannot_assign_or_modify_owner_role(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create();

        $this->actingAs($admin)
            ->patch(route('admin.roles-permissions.update', $member), [
                'role' => 'owner',
                'confirm_critical_change' => '1',
            ])
            ->assertSessionHasErrors('role');

        $this->assertSame('member', $member->refresh()->role);
    }

    public function test_owner_cannot_remove_their_own_critical_access(): void
    {
        $owner = User::factory()->owner()->create();

        $this->actingAs($owner)
            ->patch(route('admin.roles-permissions.update', $owner), [
                'role' => 'member',
                'confirm_critical_change' => '1',
            ])
            ->assertSessionHasErrors('role');

        $this->assertSame('owner', $owner->refresh()->role);
    }

    public function test_owner_and_last_critical_account_are_delete_protected(): void
    {
        $owner = User::factory()->owner()->create();
        $guard = app(AdminProtectionGuard::class);

        try {
            $guard->assertDeletionAllowed($owner, $owner);
            $this->fail('Expected owner deletion protection to reject the operation.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('user', $exception->errors());
        }

        $admin = User::factory()->admin()->create();
        $owner->forceFill(['role' => UserRole::Member->value, 'is_admin' => false])->save();

        try {
            $guard->assertDeletionAllowed($owner, $admin);
            $this->fail('Expected last administrator deletion protection to reject the operation.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString('last owner or administrator', $exception->errors()['user'][0]);
        }
    }
}
