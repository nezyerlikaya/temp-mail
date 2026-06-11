<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Localization\SaveLocaleSettingsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Localization\BulkUpdateLocalesRequest;
use App\Http\Requests\Localization\UpdateLocalesRequest;
use App\Services\Localization\LocaleReadinessService;
use App\Services\Localization\LocaleSearchService;
use App\Services\Localization\LocaleSettingsStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocaleLaunchController extends Controller
{
    public function index(
        Request $request,
        LocaleSettingsStore $store,
        LocaleSearchService $search,
        LocaleReadinessService $readiness,
    ): View {
        $request->user()?->can('admin.locale-launch-center.view') || abort(403);

        $allLocales = $store->all();
        $locales = $search->search($request->only(['q', 'state', 'direction', 'status', 'per_page']));

        return view('dashboard.locale-launch-center.index', [
            'adminUser' => $request->user(),
            'locales' => $locales,
            'summary' => $readiness->summary($allLocales),
            'readiness' => $allLocales->mapWithKeys(fn ($locale): array => [
                $locale->locale => $readiness->forLocale($locale),
            ]),
            'filters' => [
                'q' => (string) $request->query('q', ''),
                'state' => (string) $request->query('state', 'all'),
                'direction' => (string) $request->query('direction', 'all'),
                'status' => (string) $request->query('status', 'all'),
                'per_page' => (int) $request->query('per_page', 10),
            ],
            'canManageLocalization' => $request->user()?->can('manage-localization') ?? false,
        ]);
    }

    public function update(UpdateLocalesRequest $request, SaveLocaleSettingsAction $action): RedirectResponse
    {
        $action->handle($request->user(), $request->validated('locales'));

        return redirect()
            ->route('admin.locale-launch-center.index')
            ->with('status', 'Locale launch readiness saved.');
    }

    public function bulk(BulkUpdateLocalesRequest $request, SaveLocaleSettingsAction $action): RedirectResponse
    {
        $validated = $request->validated();
        $action->bulk($request->user(), $validated['locales'], $validated['action']);

        return redirect()
            ->route('admin.locale-launch-center.index')
            ->with('status', 'Visible locale selection updated.');
    }
}
