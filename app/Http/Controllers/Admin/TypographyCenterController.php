<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Typography\ActivateFontFamilyAction;
use App\Actions\Typography\DeactivateFontFamilyAction;
use App\Actions\Typography\UpdateFontAssignmentAction;
use App\Actions\Typography\UpdateFontFamilyAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Typography\ToggleFontFamilyRequest;
use App\Http\Requests\Typography\UpdateFontAssignmentRequest;
use App\Http\Requests\Typography\UpdateFontFamilyRequest;
use App\Models\FontFamily;
use App\Models\Locale;
use App\Services\Themes\ThemeManager;
use App\Services\Typography\FontAssignmentService;
use App\Services\Typography\FontCoverageService;
use App\Services\Typography\FontRegistry;
use App\Services\Typography\FontStackResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class TypographyCenterController extends Controller
{
    public function index(
        Request $request,
        FontAssignmentService $fonts,
        FontRegistry $registry,
        FontStackResolver $resolver,
        FontCoverageService $coverage,
        ThemeManager $themes,
    ): View {
        $cards = $themes->cards();
        $activeTheme = $themes->active()->slug;
        $selectedTheme = (string) $request->query('theme', $activeTheme);
        if (! $cards->pluck('slug')->contains($selectedTheme)) {
            $selectedTheme = $activeTheme;
        }

        $locales = Locale::query()->orderByDesc('is_default')->orderBy('sort_order')->orderBy('language_name')->get();
        $selectedLocale = (string) $request->query('locale', $locales->first()?->locale ?? '');
        if ($selectedLocale !== '' && ! $locales->pluck('locale')->contains($selectedLocale)) {
            $selectedLocale = $locales->first()?->locale ?? '';
        }

        $assignments = $fonts->assignments()->keyBy(fn ($assignment): string => $assignment->scope.'|'.$assignment->scope_key.'|'.$assignment->usage);
        $families = $fonts->families();
        $activeFamilies = $families->where('is_active', true)->values();
        $selectedLocaleModel = $selectedLocale !== '' ? $locales->firstWhere('locale', $selectedLocale) : null;

        return view('dashboard.typography-center.index', [
            'adminUser' => $request->user(),
            'families' => $families,
            'activeFamilies' => $activeFamilies,
            'providers' => $registry->providers(),
            'categories' => $registry->categories(),
            'scripts' => $registry->scripts(),
            'usageScopes' => $registry->usageScopes(),
            'fontDisplayOptions' => $registry->fontDisplayOptions(),
            'themes' => $cards,
            'activeTheme' => $activeTheme,
            'selectedTheme' => $selectedTheme,
            'locales' => $locales,
            'selectedLocale' => $selectedLocale,
            'selectedLocaleModel' => $selectedLocaleModel,
            'assignments' => $assignments,
            'globalResolved' => $resolver->resolve($selectedTheme),
            'localeResolved' => $selectedLocale !== '' ? $resolver->resolve($selectedTheme, $selectedLocale) : null,
            'coverageWarnings' => $this->coverageWarnings($coverage, $families, $selectedLocaleModel),
            'canManageFamilies' => $request->user()?->can('manage font families') ?? false,
            'canManageAssignments' => $request->user()?->can('manage font assignments') ?? false,
        ]);
    }

    public function updateFamily(UpdateFontFamilyRequest $request, FontFamily $fontFamily, UpdateFontFamilyAction $action): RedirectResponse
    {
        $action($fontFamily, $request->validated(), $request->user());

        return back()->with('status', 'Font readiness saved.');
    }

    public function activate(ToggleFontFamilyRequest $request, FontFamily $fontFamily, ActivateFontFamilyAction $action): RedirectResponse
    {
        $action($fontFamily, $request->user());

        return back()->with('status', 'Font family activated.');
    }

    public function deactivate(ToggleFontFamilyRequest $request, FontFamily $fontFamily, DeactivateFontFamilyAction $action): RedirectResponse
    {
        $action($fontFamily, $request->user());

        return back()->with('status', 'Font family deactivated.');
    }

    public function updateAssignments(UpdateFontAssignmentRequest $request, UpdateFontAssignmentAction $action): RedirectResponse
    {
        $validated = $request->validated();
        $action($validated['scope'], $validated['scope_key'], $validated['assignments'], $request->user());

        return back()->with('status', 'Typography assignments saved.');
    }

    /**
     * @param  Collection<int, FontFamily>  $families
     * @return array<string, array<int, array{level: string, message: string}>>
     */
    private function coverageWarnings(FontCoverageService $coverage, $families, ?Locale $locale): array
    {
        if (! $locale) {
            return [];
        }

        return $families->mapWithKeys(fn (FontFamily $family): array => [
            $family->slug => $coverage->warningsForAssignment($family, $locale),
        ])->all();
    }
}
