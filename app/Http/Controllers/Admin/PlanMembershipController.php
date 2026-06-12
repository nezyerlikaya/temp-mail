<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Billing\ActivatePlanAction;
use App\Actions\Billing\CancelMembershipAction;
use App\Actions\Billing\DeactivatePlanAction;
use App\Actions\Billing\DowngradeMembershipAction;
use App\Actions\Billing\ExtendMembershipAction;
use App\Actions\Billing\GrantMembershipAction;
use App\Actions\Billing\UpdatePlanAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\CancelMembershipRequest;
use App\Http\Requests\Billing\DowngradeMembershipRequest;
use App\Http\Requests\Billing\ExtendMembershipRequest;
use App\Http\Requests\Billing\GrantMembershipRequest;
use App\Http\Requests\Billing\MembershipFilterRequest;
use App\Http\Requests\Billing\TogglePlanRequest;
use App\Http\Requests\Billing\UpdatePlanLimitsRequest;
use App\Http\Requests\Billing\UpdatePlanRequest;
use App\Models\Membership;
use App\Models\Plan;
use App\Models\User;
use App\Services\Billing\MembershipService;
use App\Services\Billing\PlanImpactService;
use App\Services\Billing\PlanSettingsStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlanMembershipController extends Controller
{
    public function index(MembershipFilterRequest $request, PlanSettingsStore $plans, PlanImpactService $impact, MembershipService $memberships): View
    {
        $allPlans = $plans->all();
        $filters = [
            'plan_id' => (string) $request->query('plan_id', 'all'),
            'status' => (string) $request->query('status', 'all'),
            'expiring' => (string) $request->query('expiring', 'all'),
            'user' => (string) $request->query('user', ''),
        ];

        return view('dashboard.plans-memberships.index', [
            'adminUser' => $request->user(),
            'plans' => $allPlans,
            'impact' => $allPlans->mapWithKeys(fn (Plan $plan): array => [$plan->id => $impact->preview($plan)]),
            'memberships' => $memberships->search([...$request->validated(), ...$filters]),
            'filters' => $filters,
            'membershipStatuses' => $memberships->statuses(),
            'grantablePlans' => $allPlans->where('key', '!=', 'free')->where('is_active', true),
            'users' => User::query()->where('status', 'active')->orderBy('email')->limit(100)->get(['id', 'name', 'email']),
            'expiringSoon' => Membership::query()->with(['user', 'plan'])->whereIn('status', ['active', 'expiring'])->whereNotNull('ends_at')->whereBetween('ends_at', [now(), now()->addDays(7)])->orderBy('ends_at')->limit(6)->get(),
            'canUpdate' => $request->user()?->can('update plans') ?? false,
            'canUpdateLimits' => $request->user()?->can('update plan limits') ?? false,
            'canToggle' => $request->user()?->can('activate deactivate plans') ?? false,
            'canGrantMembership' => $request->user()?->can('grant membership') ?? false,
            'canExtendMembership' => $request->user()?->can('extend membership') ?? false,
            'canCancelMembership' => $request->user()?->can('cancel downgrade membership') ?? false,
        ]);
    }

    public function update(UpdatePlanRequest $request, Plan $plan, UpdatePlanAction $action): RedirectResponse
    {
        $action->details($request->user(), $plan, $request->validated());

        return back()->with('status', $plan->name.' plan saved.');
    }

    public function limits(UpdatePlanLimitsRequest $request, Plan $plan, UpdatePlanAction $action): RedirectResponse
    {
        $action->limits($request->user(), $plan, $request->validated());

        return back()->with('status', $plan->name.' limits saved.');
    }

    public function toggle(TogglePlanRequest $request, Plan $plan, ActivatePlanAction $activate, DeactivatePlanAction $deactivate): RedirectResponse
    {
        $request->validated('state') === 'active'
            ? $activate->handle($request->user(), $plan)
            : $deactivate->handle($request->user(), $plan);

        return back()->with('status', $plan->name.' plan status updated.');
    }

    public function grant(GrantMembershipRequest $request, GrantMembershipAction $action): RedirectResponse
    {
        $action->handle($request->user(), $request->validated());

        return back()->with('status', 'Membership granted.');
    }

    public function extend(ExtendMembershipRequest $request, Membership $membership, ExtendMembershipAction $action): RedirectResponse
    {
        $action->handle($request->user(), $membership, $request->validated());

        return back()->with('status', 'Membership extended.');
    }

    public function cancel(CancelMembershipRequest $request, Membership $membership, CancelMembershipAction $action): RedirectResponse
    {
        $action->handle($request->user(), $membership, $request->validated('reason'));

        return back()->with('status', 'Membership canceled.');
    }

    public function downgrade(DowngradeMembershipRequest $request, Membership $membership, DowngradeMembershipAction $action): RedirectResponse
    {
        $action->handle($request->user(), $membership);

        return back()->with('status', 'User downgraded to Free.');
    }
}
