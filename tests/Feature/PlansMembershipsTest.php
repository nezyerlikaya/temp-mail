<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\User;
use App\Services\Billing\PlanLimitResolver;
use App\Services\Billing\PlanSettingsStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlansMembershipsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_plans_memberships_renders_inside_admin_shell_and_creates_default_plans(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.plans-memberships.index'))
            ->assertOk()
            ->assertSee('Plans & Memberships')
            ->assertSee('Free')
            ->assertSee('Premium')
            ->assertSee('Business')
            ->assertSee('Manual')
            ->assertDontSee('The route, authorization boundary');

        $this->assertDatabaseHas('plans', ['key' => 'free', 'billing_provider' => 'manual']);
        $this->assertDatabaseCount('plans', 3);
        $this->assertDatabaseCount('plan_limits', 3);
    }

    public function test_plan_details_can_be_updated_without_payment_provider_integration(): void
    {
        $admin = User::factory()->admin()->create();
        $plan = $this->plan('premium');

        $this->actingAs($admin)->put(route('admin.plans-memberships.update', $plan), [
            'name' => 'Premium Plus',
            'description' => 'Updated manual plan copy.',
            'monthly_price' => '12.50',
            'yearly_price' => '120.00',
            'currency' => 'usd',
            'sort_order' => 25,
            'billing_provider' => 'manual',
        ])->assertRedirect()->assertSessionHas('status');

        $this->assertDatabaseHas('plans', [
            'id' => $plan->id,
            'name' => 'Premium Plus',
            'currency' => 'USD',
            'billing_provider' => 'manual',
        ]);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'plan.updated', 'actor_id' => $admin->id]);
    }

    public function test_limit_validation_uses_safe_bounds(): void
    {
        $admin = User::factory()->admin()->create();
        $plan = $this->plan('business');

        $this->actingAs($admin)->from(route('admin.plans-memberships.index'))->put(route('admin.plans-memberships.limits', $plan), [
            ...$this->validLimits(),
            'maximum_active_inboxes' => 0,
            'maximum_message_size_kb' => 999999,
        ])->assertRedirect(route('admin.plans-memberships.index'))
            ->assertSessionHasErrors(['maximum_active_inboxes', 'maximum_message_size_kb']);
    }

    public function test_plan_limits_can_be_updated_and_are_resolved_for_users_without_role_changes(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create(['role' => 'member', 'is_admin' => false, 'current_plan_reference' => 'business']);
        $plan = $this->plan('business');

        $this->actingAs($admin)->put(route('admin.plans-memberships.limits', $plan), [
            ...$this->validLimits(),
            'maximum_active_inboxes' => 150,
            'custom_domain_allowed' => '1',
            'api_access_allowed' => '1',
        ])->assertRedirect()->assertSessionHas('status');

        $limits = app(PlanLimitResolver::class)->forUser($member->refresh());
        $this->assertSame(150, $limits->maximum_active_inboxes);
        $this->assertTrue($limits->custom_domain_allowed);
        $this->assertFalse($member->refresh()->hasAdminAccess());
        $this->actingAs($member)->get(route('admin.plans-memberships.index'))->assertForbidden();
        $this->assertDatabaseHas('user_audit_events', ['event' => 'plan.limits_updated', 'actor_id' => $admin->id]);
    }

    public function test_free_plan_cannot_be_disabled_and_plans_cannot_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $free = $this->plan('free');

        $this->actingAs($admin)->post(route('admin.plans-memberships.status', $free), ['state' => 'inactive'])
            ->assertSessionHasErrors('plan');
        $this->assertTrue($free->refresh()->is_active);

        collect(app('router')->getRoutes())->each(function ($route): void {
            $this->assertFalse(
                str_contains((string) $route->getName(), 'plans-memberships')
                && in_array('DELETE', $route->methods(), true),
                'Plans & Memberships must not expose delete routes.'
            );
        });
    }

    public function test_at_least_one_public_plan_remains_active(): void
    {
        $admin = User::factory()->admin()->create();
        $premium = $this->plan('premium');
        $business = $this->plan('business');
        $free = $this->plan('free');
        $free->forceFill(['is_active' => false])->save();
        $business->forceFill(['is_active' => false])->save();

        $this->actingAs($admin)->post(route('admin.plans-memberships.status', $premium), ['state' => 'inactive'])
            ->assertSessionHasErrors('plan');
        $this->assertTrue($premium->refresh()->is_active);
    }

    public function test_admin_or_owner_can_manage_but_product_membership_does_not_grant_access(): void
    {
        $premiumMember = User::factory()->create([
            'role' => 'member',
            'current_plan_reference' => 'premium',
            'membership_status' => 'active',
            'is_admin' => false,
        ]);
        $admin = User::factory()->admin()->create();

        $this->actingAs($premiumMember)->get(route('admin.plans-memberships.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.plans-memberships.index'))->assertOk();
    }

    /** @return array<string, mixed> */
    private function validLimits(): array
    {
        return [
            'maximum_active_inboxes' => 20,
            'inbox_lifetime_minutes' => 60,
            'maximum_messages_per_inbox' => 200,
            'maximum_message_size_kb' => 20480,
            'custom_alias_allowed' => '1',
            'custom_domain_allowed' => '0',
            'api_access_allowed' => '1',
            'api_request_limit' => 1000,
            'ads_enabled' => '0',
        ];
    }

    private function plan(string $key): Plan
    {
        app(PlanSettingsStore::class)->ensureDefaults();

        return Plan::query()->where('key', $key)->firstOrFail();
    }
}
