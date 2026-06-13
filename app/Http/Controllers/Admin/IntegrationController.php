<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Integrations\ActivateIntegrationAction;
use App\Actions\Integrations\DeactivateIntegrationAction;
use App\Actions\Integrations\UpdateIntegrationSettingsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Integrations\IntegrationFilterRequest;
use App\Http\Requests\Integrations\ToggleIntegrationRequest;
use App\Http\Requests\Integrations\UpdateIntegrationSettingsRequest;
use App\Services\Integrations\IntegrationDependencyResolver;
use App\Services\Integrations\IntegrationFieldRegistry;
use App\Services\Integrations\IntegrationRegistry;
use App\Services\Integrations\IntegrationSettingsStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class IntegrationController extends Controller
{
    public function index(
        IntegrationFilterRequest $request,
        IntegrationRegistry $registry,
        IntegrationSettingsStore $settings,
        IntegrationDependencyResolver $dependencies,
    ): View {
        $category = (string) $request->validated('category', 'all');
        $environment = (string) $request->validated('environment', 'sandbox');
        $cards = $settings->cards($category, $environment);
        $selectedKey = (string) $request->validated('integration', $cards->first()['key'] ?? $registry->integrations()->first()['key']);
        $selected = $settings->present($selectedKey, $environment);

        return view('dashboard.integrations.index', [
            'adminUser' => $request->user(),
            'categories' => $registry->categories(),
            'environments' => $registry->environments(),
            'activeCategory' => $category,
            'activeEnvironment' => $environment,
            'integrations' => $cards,
            'selected' => $selected,
            'dependency' => $dependencies->warningsFor($selected),
            'canConfigure' => $request->user()?->can('configure integrations') ?? false,
            'canToggle' => $request->user()?->can('activate deactivate integrations') ?? false,
            'canReveal' => $request->user()?->can('reveal integration secret') ?? false,
        ]);
    }

    public function update(
        UpdateIntegrationSettingsRequest $request,
        string $integration,
        UpdateIntegrationSettingsAction $action,
    ): RedirectResponse {
        $validated = $request->validated();
        $validated['settings'] = $request->input('settings', []);
        $validated['secrets'] = $request->input('secrets', []);

        $action->handle($request->user(), $integration, (string) $validated['environment'], $validated);

        return redirect()
            ->route('admin.integrations.index', ['integration' => $integration, 'environment' => $validated['environment']])
            ->with('status', 'Integration configuration saved.');
    }

    public function activate(ToggleIntegrationRequest $request, string $integration, ActivateIntegrationAction $action): RedirectResponse
    {
        $action->handle($request->user(), $integration, (string) $request->validated('environment'));

        return back()->with('status', 'Integration activated. Configuration was preserved.');
    }

    public function deactivate(ToggleIntegrationRequest $request, string $integration, DeactivateIntegrationAction $action): RedirectResponse
    {
        $action->handle($request->user(), $integration, (string) $request->validated('environment'));

        return back()->with('status', 'Integration deactivated. Configuration was preserved.');
    }

    public function reveal(
        Request $request,
        IntegrationRegistry $registry,
        IntegrationFieldRegistry $fields,
        IntegrationSettingsStore $settings,
        string $integration,
        string $field,
    ): JsonResponse {
        Gate::authorize('reveal integration secret');
        abort_unless($registry->find($integration), 404);
        $definition = collect($fields->fields($integration))->firstWhere('key', $field);
        abort_unless(($definition['type'] ?? null) === 'secret' && ($definition['reveal'] ?? true), 404);

        $environment = (string) $request->query('environment', 'sandbox');

        return response()->json([
            'value' => $settings->secret($integration, $environment, $field),
        ]);
    }
}
