<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Security\TestAkismetAction;
use App\Actions\Security\TestBotProviderAction;
use App\Actions\Security\UpdateAkismetSettingsAction;
use App\Actions\Security\UpdateBotProtectionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Security\TestSecurityProviderRequest;
use App\Http\Requests\Security\UpdateAkismetRequest;
use App\Http\Requests\Security\UpdateBotProtectionRequest;
use App\Services\Security\AkismetSpamService;
use App\Services\Security\BotProtectionService;
use App\Services\Security\BotProviderRegistry;
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
    ): View {
        Gate::authorize('view security settings');

        return view('dashboard.security-defense-center.index', [
            'adminUser' => $request->user(),
            'botSettings' => $settings->bot(),
            'akismetSettings' => $settings->akismet(),
            'providers' => $registry->providers(),
            'protectedForms' => $registry->protectedForms(),
            'failModes' => $registry->failModes(),
            'botReadiness' => $botProtection->readiness(),
            'akismetReadiness' => $akismet->readiness(),
            'canUpdateSecurity' => $request->user()?->can('update security settings') ?? false,
            'canRevealSecrets' => $request->user()?->can('reveal security secret') ?? false,
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
