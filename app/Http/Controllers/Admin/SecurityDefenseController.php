<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Security\ForceLogoutAllSessionsAction;
use App\Actions\Security\TestAkismetAction;
use App\Actions\Security\TestBotProviderAction;
use App\Actions\Security\UpdateAdminSecurityAction;
use App\Actions\Security\UpdateAkismetSettingsAction;
use App\Actions\Security\UpdateBotProtectionAction;
use App\Actions\Security\UpdateIpAccessAction;
use App\Actions\Security\UpdateRateLimitsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Security\ForceLogoutSessionsRequest;
use App\Http\Requests\Security\TestSecurityProviderRequest;
use App\Http\Requests\Security\UpdateAdminSecurityRequest;
use App\Http\Requests\Security\UpdateAkismetRequest;
use App\Http\Requests\Security\UpdateBotProtectionRequest;
use App\Http\Requests\Security\UpdateIpAccessRequest;
use App\Http\Requests\Security\UpdateRateLimitsRequest;
use App\Services\Security\AdminAccessGuard;
use App\Services\Security\AkismetSpamService;
use App\Services\Security\BotProtectionService;
use App\Services\Security\BotProviderRegistry;
use App\Services\Security\FailedLoginService;
use App\Services\Security\IpAccessService;
use App\Services\Security\RateLimitPolicyService;
use App\Services\Security\RateLimitPolicyStore;
use App\Services\Security\SecuritySettingsStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SecurityDefenseController extends Controller
{
    public function index(
        Request $request,
        SecuritySettingsStore $settings,
        BotProviderRegistry $registry,
        BotProtectionService $botProtection,
        AkismetSpamService $akismet,
        RateLimitPolicyStore $rateLimitStore,
        RateLimitPolicyService $rateLimitPolicy,
        IpAccessService $ipAccessService,
        AdminAccessGuard $adminAccessGuard,
        FailedLoginService $failedLogins,
    ): View {
        Gate::authorize('view security settings');

        $ipAccess = $rateLimitStore->ipAccess();
        $adminAccess = $rateLimitStore->adminAccess();

        return view('dashboard.security-defense-center.index', [
            'adminUser' => $request->user(),
            'botSettings' => $settings->bot(),
            'akismetSettings' => $settings->akismet(),
            'rateLimitPolicies' => $rateLimitStore->policies(),
            'rateLimitStrategies' => $rateLimitStore->strategies(),
            'rateLimitReadiness' => $rateLimitPolicy->readiness(),
            'rateLimitStatus' => $rateLimitPolicy->summaryStatus(),
            'ipAccess' => $ipAccess,
            'ipAccessReadiness' => $ipAccessService->readiness(),
            'adminAccess' => $adminAccess,
            'adminAccessReadiness' => $adminAccessGuard->readiness($adminAccess, $ipAccess),
            'failedLoginSummary' => $failedLogins->summary(),
            'providers' => $registry->providers(),
            'protectedForms' => $registry->protectedForms(),
            'failModes' => $registry->failModes(),
            'botReadiness' => $botProtection->readiness(),
            'akismetReadiness' => $akismet->readiness(),
            'canUpdateSecurity' => $request->user()?->can('update security settings') ?? false,
            'canRevealSecrets' => $request->user()?->can('reveal security secret') ?? false,
            'canManageRateLimits' => $request->user()?->can('manage rate limits') ?? false,
            'canManageAdminSecurity' => $request->user()?->can('manage admin security') ?? false,
            'canForceLogout' => $request->user()?->can('force logout sessions') ?? false,
            'canViewFailedLogins' => $request->user()?->can('view failed login history') ?? false,
        ]);
    }

    public function updateBot(UpdateBotProtectionRequest $request, UpdateBotProtectionAction $action): RedirectResponse
    {
        $action->handle($request->user(), $request->validated());

        return redirect()->route('admin.security-defense-center.index')->with('status', 'Bot protection settings saved.');
    }

    public function updateAkismet(UpdateAkismetRequest $request, UpdateAkismetSettingsAction $action): RedirectResponse
    {
        $action->handle($request->user(), $request->validated());

        return redirect()->route('admin.security-defense-center.index')->with('status', 'Akismet settings saved.');
    }

    public function updateRateLimits(UpdateRateLimitsRequest $request, UpdateRateLimitsAction $action): RedirectResponse
    {
        $action->handle($request->user(), $request->validated());

        return redirect()->route('admin.security-defense-center.index')->with('status', 'Rate limit policies saved.');
    }

    public function updateIpAccess(UpdateIpAccessRequest $request, UpdateIpAccessAction $action): RedirectResponse
    {
        $action->handle($request->user(), $request->validated());

        return redirect()->route('admin.security-defense-center.index')->with('status', 'IP access settings saved.');
    }

    public function updateAdminSecurity(UpdateAdminSecurityRequest $request, UpdateAdminSecurityAction $action): RedirectResponse
    {
        $action->handle($request->user(), $request->validated());

        return redirect()->route('admin.security-defense-center.index')->with('status', 'Admin access security saved.');
    }

    public function forceLogout(ForceLogoutSessionsRequest $request, ForceLogoutAllSessionsAction $action): RedirectResponse
    {
        $count = $action->handle($request->user(), $request->session()->getId());

        return redirect()->route('admin.security-defense-center.index')->with('status', "{$count} authenticated sessions were logged out.");
    }

    public function test(TestSecurityProviderRequest $request, TestBotProviderAction $bot, TestAkismetAction $akismet): RedirectResponse
    {
        $result = $request->validated('target') === 'akismet'
            ? $akismet->handle($request->user())
            : $bot->handle($request->user());

        return redirect()->route('admin.security-defense-center.index')->with('test_status', $result);
    }

    public function reveal(Request $request, SecuritySettingsStore $settings, string $group, string $field): JsonResponse
    {
        Gate::authorize('reveal security secret');
        abort_unless(in_array($group, ['bot_protection', 'akismet'], true), 404);
        abort_unless(in_array($field, ['site_key', 'secret_key', 'api_key'], true), 404);

        return response()->json([
            'value' => $settings->secret($group, $field),
        ]);
    }
}
