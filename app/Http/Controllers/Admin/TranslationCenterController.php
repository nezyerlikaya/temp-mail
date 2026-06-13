<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Translations\CreateTranslationSourceAction;
use App\Actions\Translations\PublishTranslationsAction;
use App\Actions\Translations\ReviewTranslationsAction;
use App\Actions\Translations\SaveTranslationsAction;
use App\Actions\Translations\UpdateTranslationSourceAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Translations\PublishTranslationsRequest;
use App\Http\Requests\Translations\ReviewTranslationsRequest;
use App\Http\Requests\Translations\StoreTranslationSourceRequest;
use App\Http\Requests\Translations\TranslationEditorFilterRequest;
use App\Http\Requests\Translations\UpdateTranslationSourceRequest;
use App\Http\Requests\Translations\UpdateTranslationsRequest;
use App\Models\Locale;
use App\Models\TranslationSource;
use App\Services\Translations\TranslationCoverageService;
use App\Services\Translations\TranslationEditorService;
use App\Services\Translations\TranslationGroupRegistry;
use App\Services\Translations\TranslationSearchService;
use App\Services\Translations\TranslationStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TranslationCenterController extends Controller
{
    public function index(
        TranslationEditorFilterRequest $request,
        TranslationStore $store,
        TranslationSearchService $search,
        TranslationGroupRegistry $groups,
        TranslationEditorService $editor,
        TranslationCoverageService $coverage,
    ): View {
        $store->syncRegistry();
        $filters = $request->filters();
        $targetLocales = $editor->activeTargetLocales();
        $selectedLocale = $targetLocales->firstWhere('locale', $filters['locale']);
        $isEditor = $filters['mode'] === 'editor' && $selectedLocale instanceof Locale;
        $groupMap = $groups->groups();

        return view('dashboard.translation-center.index', [
            'adminUser' => $request->user(),
            'groups' => $groupMap,
            'sources' => $isEditor ? $editor->sources($selectedLocale, $filters) : $search->search($filters),
            'filters' => $filters,
            'summary' => $store->summary(),
            'targetLocales' => $targetLocales,
            'selectedLocale' => $selectedLocale,
            'isEditor' => $isEditor,
            'coverage' => $isEditor ? $coverage->forLocale($selectedLocale) : null,
            'groupCoverage' => $isEditor ? $coverage->byGroup($selectedLocale, $groupMap) : [],
            'canManageSources' => $request->user()?->can('manage translation sources') ?? false,
            'canEditTranslations' => $request->user()?->can('edit translations') ?? false,
            'canReviewTranslations' => $request->user()?->can('review translations') ?? false,
            'canPublishTranslations' => $request->user()?->can('publish translations') ?? false,
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

    public function save(UpdateTranslationsRequest $request, SaveTranslationsAction $action): RedirectResponse
    {
        $locale = Locale::query()->where('locale', $request->validated('locale'))->firstOrFail();
        $count = $action->handle($request->user(), $locale, $request->validated('translations'));

        return $this->editorRedirect($locale->locale)->with('status', "{$count} translations saved as draft.");
    }

    public function review(ReviewTranslationsRequest $request, ReviewTranslationsAction $action): RedirectResponse
    {
        $locale = Locale::query()->where('locale', $request->validated('locale'))->firstOrFail();
        $count = $action->handle($request->user(), $locale, $request->validated('source_ids'));

        return $this->editorRedirect($locale->locale)->with('status', "{$count} translations marked reviewed.");
    }

    public function publish(PublishTranslationsRequest $request, PublishTranslationsAction $action): RedirectResponse
    {
        $locale = Locale::query()->where('locale', $request->validated('locale'))->firstOrFail();
        $count = $action->handle($request->user(), $locale, $request->validated('source_ids'));

        return $this->editorRedirect($locale->locale)->with('status', "{$count} translations published.");
    }

    private function editorRedirect(string $locale): RedirectResponse
    {
        return redirect()->route('admin.translation-center.index', [
            'mode' => 'editor',
            'locale' => $locale,
        ]);
    }
}
