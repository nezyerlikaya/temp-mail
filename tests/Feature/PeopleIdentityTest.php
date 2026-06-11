<?php

namespace Tests\Feature;

use App\Events\UserIdentityUpdated;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PeopleIdentityTest extends TestCase
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

    public function test_admin_can_view_people_identity_directory(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create(['name' => 'Ada Member']);

        $this->actingAs($admin)
            ->get(route('admin.people-identity.index'))
            ->assertOk()
            ->assertSee('People & Identity')
            ->assertSee('Ada Member')
            ->assertSee('Identity directory');
    }

    public function test_normal_user_cannot_access_people_identity_pages(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)
            ->get(route('admin.people-identity.index'))
            ->assertForbidden();
    }

    public function test_directory_supports_identity_role_and_status_filters(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create([
            'name' => 'Active Author',
            'email' => 'author@example.com',
            'role' => 'author',
            'status' => 'active',
        ]);
        User::factory()->create([
            'name' => 'Suspended Member',
            'email' => 'suspended@example.com',
            'role' => 'member',
            'status' => 'suspended',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.people-identity.index', [
                'search' => 'author@example.com',
                'role' => 'author',
                'status' => 'active',
            ]))
            ->assertOk()
            ->assertSee('Active Author')
            ->assertDontSee('Suspended Member');
    }

    public function test_admin_can_view_and_edit_identity_foundation(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create(['display_name' => 'Public Name']);

        $this->actingAs($admin)
            ->get(route('admin.people-identity.show', $member))
            ->assertOk()
            ->assertSee('Public Name')
            ->assertSee('Account readiness');

        $this->actingAs($admin)
            ->get(route('admin.people-identity.edit', $member))
            ->assertOk()
            ->assertSee('Edit identity')
            ->assertSee('Role classification')
            ->assertSee('Account status');
    }

    public function test_identity_update_persists_and_dispatches_audit_ready_event(): void
    {
        Event::fake([UserIdentityUpdated::class]);

        $admin = User::factory()->admin()->create();
        $member = User::factory()->create();

        $this->actingAs($admin)
            ->put(route('admin.people-identity.update', $member), [
                'name' => 'Updated Person',
                'display_name' => 'Updated',
                'username' => 'updated-person',
                'email' => 'updated@example.com',
                'status' => 'active',
                'role' => 'author',
                'timezone' => 'Europe/Istanbul',
                'language_preference' => 'tr',
                'bio' => 'Profile biography ready for future author pages.',
                'website' => 'https://example.com',
            ])
            ->assertRedirect(route('admin.people-identity.show', $member));

        $this->assertDatabaseHas('users', [
            'id' => $member->id,
            'name' => 'Updated Person',
            'username' => 'updated-person',
            'role' => 'author',
            'status' => 'active',
        ]);

        Event::assertDispatched(UserIdentityUpdated::class, fn (UserIdentityUpdated $event): bool => $event->actor->is($admin)
            && $event->subject->is($member)
            && array_key_exists('name', $event->changes));
    }

    public function test_identity_validation_errors_render_accessible_summary(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create();

        $this->actingAs($admin)
            ->followingRedirects()
            ->from(route('admin.people-identity.edit', $member))
            ->put(route('admin.people-identity.update', $member), [
                'name' => '',
                'email' => 'invalid-email',
                'status' => 'unknown',
                'role' => 'premium',
                'timezone' => 'Mars/Olympus',
                'language_preference' => 'xx',
            ])
            ->assertOk()
            ->assertSee('Please fix the highlighted fields.')
            ->assertSee('aria-invalid="true"', false)
            ->assertSee('role="alert"', false);
    }

    public function test_admin_cannot_suspend_their_own_account(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('admin.people-identity.update', $admin), [
                'name' => $admin->name,
                'display_name' => $admin->display_name,
                'username' => $admin->username,
                'email' => $admin->email,
                'status' => 'suspended',
                'role' => 'admin',
                'timezone' => 'UTC',
                'language_preference' => 'en',
                'bio' => null,
                'website' => null,
            ])
            ->assertSessionHasErrors('status');

        $this->assertSame('active', $admin->refresh()->status);
    }

    public function test_suspended_user_cannot_sign_in(): void
    {
        $user = User::factory()->suspended()->create([
            'email' => 'suspended@example.com',
            'password' => 'Secure123!',
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'Secure123!',
        ])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
