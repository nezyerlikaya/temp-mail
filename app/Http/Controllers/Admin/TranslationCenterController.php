<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Translations\CreateTranslationSourceAction;
use App\Actions\Translations\UpdateTranslationSourceAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Translations\StoreTranslationSourceRequest;
use App\Http\Requests\Translations\TranslationSourceFilterRequest;
use App\Http\Requests\Translations\UpdateTranslationSourceRequest;
use App\Models\TranslationSource;
use App\Services\Translations\TranslationGroupRegistry;
use App\Services\Translations\TranslationSearchService;
use App\Services\Translations\TranslationStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TranslationCenterController extends Controller
{
    public function index(
        TranslationSourceFilterRequest $request,
        TranslationStore $store,
        TranslationSearchService $search,
        TranslationGroupRegistry $groups,
    ): View {
        $store->syncRegistry();
        $filters = $request->filters();

        return view('dashboard.translation-center.index', [
            'adminUser' => $request->user(),
            'groups' => $groups->groups(),
            'sources' => $search->search($filters),
            'filters' => $filters,
            'summary' => $store->summary(),
            'canManageSources' => $request->user()?->can('manage translation sources') ?? false,
        ]);
    }

    public function store(StoreTranslationSourceRequest $request, CreateTranslationSourceAction $action): RedirectResponse
    {
        $action->handle($request->user(), $request->payload());

        return redirect()->route('admin.translation-center.index')->with('status', 'Translation source key created.');
    }

    public function update(UpdateTranslationSourceRequest $request, TranslationSource $translationSource, UpdateTranslationSourceAction $action): RedirectResponse
    {
        $action->handle($request->user(), $translationSource, $request->payload());

        return redirect()->route('admin.translation-center.index', $request->only(['group', 'q', 'requirement', 'state', 'missing']))->with('status', 'Translation source key updated.');
    }
}
