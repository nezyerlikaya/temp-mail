<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Localization\LocaleBulkAction;
use App\Actions\Localization\SaveLocaleSettingsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Localization\BulkUpdateLocalesRequest;
use App\Http\Requests\Localization\UpdateLocalesRequest;
use App\Http\Requests\Localization\UpdateLocaleStatusRequest;
use App\Models\Locale;
use App\Services\Localization\LocaleLaunchQueueService;
use App\Services\Localization\LocalePreviewUrlResolver;
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
        LocaleLaunchQueueService $queue,
        LocalePreviewUrlResolver $urls,
    ): View {
        $request->user()?->can('admin.locale-launch-center.view') || abort(403);

        $allLocales = $store->all();
        $locales = $search->search($request->only(['q', 'state', 'direction', 'status', 'readiness', 'per_page']));
        $preparedReadiness = $allLocales->mapWithKeys(fn (Locale $locale): array => [
            $locale->locale => $readiness->forLocale($locale),
        ]);
        $preparedUrls = $allLocales->mapWithKeys(fn (Locale $locale): array => [
            $locale->locale => $urls->urls($locale),
        ]);

        return view('dashboard.locale-launch-center.index', [
            'adminUser' => $request->user(),
            'locales' => $locales,
            'summary' => $readiness->summary($allLocales),
            'readiness' => $preparedReadiness,
            'localeUrls' => $preparedUrls,
            'launchQueue' => $queue->build($allLocales),
            'filters' => [
                'q' => (string) $request->query('q', ''),
                'state' => (string) $request->query('state', 'all'),
                'direction' => (string) $request->query('direction', 'all'),
                'status' => (string) $request->query('status', 'all'),
                'readiness' => (string) $request->query('readiness', 'all'),
                'per_page' => (int) $request->query('per_page', 10),
            ],
            'canManageLocalization' => $request->user()?->can('manage-localization') ?? false,
            'canPublishLocales' => $request->user()?->can('admin.locale-launch-center.publish') ?? false,
        ]);
    }

    public function update(UpdateLocalesRequest $request, SaveLocaleSettingsAction $action): RedirectResponse
    {
        $action->handle($request->user(), $request->validated('locales'));

        return redirect()
            ->route('admin.locale-launch-center.index')
            ->with('status', 'Locale launch readiness saved.');
    }

    public function bulk(BulkUpdateLocalesRequest $request, LocaleBulkAction $action): RedirectResponse
    {
        $validated = $request->validated();
        $action->handle($request->user(), $validated['locales'], $validated['action']);

        return redirect()
            ->route('admin.locale-launch-center.index')
            ->with('status', 'Visible locale selection updated.');
    }

    public function status(UpdateLocaleStatusRequest $request, Locale $locale, SaveLocaleSettingsAction $action): RedirectResponse
    {
        try {
            $action->updateStatus($request->user(), $locale->locale, $request->validated('status_action'));
        } catch (\RuntimeException $exception) {
            return redirect()
                ->route('admin.locale-launch-center.index')
                ->with('warning', $exception->getMessage());
        }

        return redirect()
            ->route('admin.locale-launch-center.index')
            ->with('status', 'Locale status updated.');
    }
}
