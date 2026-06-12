<?php

namespace Tests\Feature;

use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use App\Services\Billing\MembershipExpiryService;
use App\Services\Billing\PlanLimitResolver;
use App\Services\Billing\PlanSettingsStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MembershipGrantsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_one_month_premium_membership_can_be_granted_manually(): void
    {
        [$admin, $member, $premium] = $this->actors();
        $start = now()->startOfMinute();

        $this->actingAs($admin)->post(route('admin.plans-memberships.memberships.grant'), [
            'user_id' => $member->id,
            'plan_id' => $premium->id,
            'preset' => 'one_month',
            'starts_at' => $start->format('Y-m-d\TH:i'),
            'grace_period_days' => 0,
            'grant_note' => 'Support courtesy month.',
        ])->assertRedirect()->assertSessionHas('status', 'Membership granted.');

        $membership = Membership::query()->with('plan')->firstOrFail();
        $this->assertSame('premium', $membership->plan->key);
        $this->assertSame('active', $membership->status);
        $this->assertSame($start->copy()->addMonthNoOverflow()->format('Y-m-d H:i'), $membership->ends_at->format('Y-m-d H:i'));
        $member->refresh();
        $this->assertSame('premium', $member->current_plan_reference);
        $this->assertFalse($member->hasAdminAccess());
        $this->assertDatabaseHas('user_audit_events', ['event' => 'membership.granted', 'actor_id' => $admin->id]);
    }

    public function test_custom_dates_validate_end_after_start_and_grace_period_maximum(): void
    {
        [$admin, $member, $premium] = $this->actors();

        $this->actingAs($admin)->from(route('admin.plans-memberships.index'))->post(route('admin.plans-memberships.memberships.grant'), [
            'user_id' => $member->id,
            'plan_id' => $premium->id,
            'preset' => 'custom',
            'starts_at' => now()->addDay()->format('Y-m-d\TH:i'),
            'ends_at' => now()->format('Y-m-d\TH:i'),
            'grace_period_days' => 4,
        ])->assertRedirect(route('admin.plans-memberships.index'))
            ->assertSessionHasErrors(['ends_at', 'grace_period_days']);
    }

    public function test_membership_can_be_extended_and_canceled(): void
    {
        [$admin, $member, $premium] = $this->actors();
        $membership = $this->membership($member, $premium, now(), now()->addDays(5), $admin);

        $this->actingAs($admin)->put(route('admin.plans-memberships.memberships.extend', $membership), [
            'preset' => 'one_month',
            'grace_period_days' => 2,
        ])->assertRedirect()->assertSessionHas('status', 'Membership extended.');
        $this->assertSame(2, $membership->refresh()->grace_period_days);
        $this->assertTrue($membership->ends_at->greaterThan(now()->addDays(30)));
        $this->assertDatabaseHas('user_audit_events', ['event' => 'membership.extended', 'actor_id' => $admin->id]);

        $this->actingAs($admin)->post(route('admin.plans-memberships.memberships.cancel', $membership), [
            'confirmation' => 'CANCEL',
            'reason' => 'Manual test cancellation.',
        ])->assertRedirect()->assertSessionHas('status', 'Membership canceled.');
        $this->assertSame('canceled', $membership->refresh()->status);
        $this->assertSame('free', $member->refresh()->current_plan_reference);
        $this->assertSame('canceled', $member->membership_status);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'membership.canceled', 'actor_id' => $admin->id]);
    }

    public function test_downgrade_to_free_flow_updates_user_without_role_changes(): void
    {
        [$admin, $member, $premium] = $this->actors();
        $membership = $this->membership($member, $premium, now(), now()->addMonth(), $admin);

        $this->actingAs($admin)->post(route('admin.plans-memberships.memberships.downgrade', $membership), [
            'confirmation' => 'DOWNGRADE',
        ])->assertRedirect()->assertSessionHas('status', 'User downgraded to Free.');

        $member->refresh();
        $this->assertSame('free', $member->current_plan_reference);
        $this->assertSame('member', $member->role);
        $this->assertFalse($member->hasAdminAccess());
        $this->assertDatabaseHas('user_audit_events', ['event' => 'membership.user_downgraded_to_free', 'actor_id' => $admin->id]);
    }

    public function test_expiration_process_resolves_premium_to_free_and_dispatches_notifications(): void
    {
        [$admin, $member, $premium] = $this->actors();
        $owner = User::factory()->owner()->create();
        $membership = $this->membership($member, $premium, now()->subMonths(2), now()->subDay(), $admin);

        $result = app(MembershipExpiryService::class)->process($admin);

        $this->assertSame(1, $result['expired']);
        $this->assertSame('expired', $membership->refresh()->status);
        $this->assertSame('free', $member->refresh()->current_plan_reference);
        $this->assertDatabaseHas('system_notifications', ['event_key' => 'premium_expired', 'recipient_user_id' => $owner->id]);
        $this->assertDatabaseHas('user_audit_events', ['event' => 'membership.expired']);
    }

    public function test_expiring_soon_membership_is_marked_and_notified(): void
    {
        [$admin, $member, $premium] = $this->actors();
        $owner = User::factory()->owner()->create();
        $membership = $this->membership($member, $premium, now()->subDay(), now()->addDays(3), $admin);

        $result = app(MembershipExpiryService::class)->process($admin);

        $this->assertSame(1, $result['expiring']);
        $this->assertSame('expiring', $membership->refresh()->status);
        $this->assertDatabaseHas('system_notifications', ['event_key' => 'premium_expiring_soon', 'recipient_user_id' => $owner->id]);
    }

    public function test_expired_user_resolves_to_free_limits_even_before_processing(): void
    {
        [$admin, $member, $premium] = $this->actors();
        $this->membership($member, $premium, now()->subMonths(2), now()->subDay(), $admin);
        $member->forceFill(['current_plan_reference' => 'premium', 'membership_status' => 'active', 'premium_ends_at' => now()->subDay()])->save();

        $limits = app(PlanLimitResolver::class)->forUser($member->refresh());

        $this->assertSame(3, $limits->maximum_active_inboxes);
    }

    public function test_only_owner_or_admin_can_grant_extend_or_cancel_memberships(): void
    {
        [$admin, $member, $premium] = $this->actors();
        $moderator = User::factory()->create(['role' => 'moderator', 'is_admin' => true]);
        $membership = $this->membership($member, $premium, now(), now()->addMonth(), $admin);

        $this->actingAs($moderator)->post(route('admin.plans-memberships.memberships.grant'), [
            'user_id' => $member->id,
            'plan_id' => $premium->id,
            'preset' => 'one_month',
            'starts_at' => now()->format('Y-m-d\TH:i'),
        ])->assertForbidden();
        $this->actingAs($moderator)->put(route('admin.plans-memberships.memberships.extend', $membership), ['preset' => 'one_month'])->assertForbidden();
        $this->actingAs($moderator)->post(route('admin.plans-memberships.memberships.cancel', $membership), ['confirmation' => 'CANCEL'])->assertForbidden();
    }

    public function test_membership_filters_render_inside_plans_page(): void
    {
        [$admin, $member, $premium] = $this->actors();
        $this->membership($member, $premium, now(), now()->addDays(3), $admin);

        $this->actingAs($admin)->get(route('admin.plans-memberships.index', [
            'status' => 'active',
            'expiring' => 'soon',
            'user' => $member->email,
        ]))->assertOk()
            ->assertSee('Manual memberships')
            ->assertSee($member->email)
            ->assertSee('Expiring soon');
    }

    /** @return array{User, User, Plan} */
    private function actors(): array
    {
        app(PlanSettingsStore::class)->ensureDefaults();

        return [
            User::factory()->admin()->create(),
            User::factory()->create(['role' => 'member', 'is_admin' => false]),
            Plan::query()->where('key', 'premium')->firstOrFail(),
        ];
    }

    private function membership(User $user, Plan $plan, mixed $startsAt, mixed $endsAt, User $admin): Membership
    {
        $membership = Membership::query()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'granted_by' => $admin->id,
            'grant_note' => 'Fixture',
        ])->load('plan', 'user');

        $user->forceFill([
            'current_plan_reference' => $plan->key,
            'membership_status' => 'active',
            'premium_starts_at' => $membership->starts_at,
            'premium_ends_at' => $membership->ends_at,
            'membership_granted_by' => $admin->id,
        ])->save();

        return $membership;
    }
}
