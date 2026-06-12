<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Billing\ActivatePlanAction;
use App\Actions\Billing\DeactivatePlanAction;
use App\Actions\Billing\UpdatePlanAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\TogglePlanRequest;
use App\Http\Requests\Billing\UpdatePlanLimitsRequest;
use App\Http\Requests\Billing\UpdatePlanRequest;
use App\Models\Plan;
use App\Services\Billing\PlanImpactService;
use App\Services\Billing\PlanSettingsStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanMembershipController extends Controller
{
    public function index(Request $request, PlanSettingsStore $plans, PlanImpactService $impact): View
    {
        $allPlans = $plans->all();

        return view('dashboard.plans-memberships.index', [
            'adminUser' => $request->user(),
            'plans' => $allPlans,
            'impact' => $allPlans->mapWithKeys(fn (Plan $plan): array => [$plan->id => $impact->preview($plan)]),
            'canUpdate' => $request->user()?->can('update plans') ?? false,
            'canUpdateLimits' => $request->user()?->can('update plan limits') ?? false,
            'canToggle' => $request->user()?->can('activate deactivate plans') ?? false,
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
}
