<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Typography\ActivateFontFamilyAction;
use App\Actions\Typography\DeactivateFontFamilyAction;
use App\Actions\Typography\ResetLocaleFontOverrideAction;
use App\Actions\Typography\UpdateFontAssignmentAction;
use App\Actions\Typography\UpdateFontFamilyAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Typography\PreviewTypographyRequest;
use App\Http\Requests\Typography\ResetLocaleFontOverrideRequest;
use App\Http\Requests\Typography\ToggleFontFamilyRequest;
use App\Http\Requests\Typography\UpdateFontAssignmentRequest;
use App\Http\Requests\Typography\UpdateFontFamilyRequest;
use App\Models\FontFamily;
use App\Models\Locale;
use App\Services\Themes\ThemeManager;
use App\Services\Typography\FontAssignmentService;
use App\Services\Typography\FontCoverageService;
use App\Services\Typography\FontFallbackSimulator;
use App\Services\Typography\FontPairingService;
use App\Services\Typography\FontPerformanceService;
use App\Services\Typography\FontPreviewService;
use App\Services\Typography\FontRegistry;
use App\Services\Typography\FontStackResolver;
use App\Services\Typography\TypographyReadinessService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;

class TypographyCenterController extends Controller
{
    public function index(
        PreviewTypographyRequest $request,
        FontAssignmentService $fonts,
        FontRegistry $registry,
        FontStackResolver $resolver,
        FontCoverageService $coverage,
        FontPreviewService $preview,
        FontPerformanceService $performance,
        FontPairingService $pairing,
        FontFallbackSimulator $fallbacks,
        TypographyReadinessService $readiness,
        ThemeManager $themes,
    ): View {
        $cards = $themes->cards();
        $activeTheme = $themes->active()->slug;
        $selectedTheme = (string) $request->validated('theme', $activeTheme);
        if (! $cards->pluck('slug')->contains($selectedTheme)) {
            $selectedTheme = $activeTheme;
        }

        $locales = Locale::query()->orderByDesc('is_default')->orderBy('sort_order')->orderBy('language_name')->get();
        $selectedLocale = (string) $request->validated('locale', $locales->first()?->locale ?? '');
        if ($selectedLocale !== '' && ! $locales->pluck('locale')->contains($selectedLocale)) {
            $selectedLocale = $locales->first()?->locale ?? '';
        }

        $assignments = $fonts->assignments()->keyBy(fn ($assignment): string => $assignment->scope.'|'.$assignment->scope_key.'|'.$assignment->usage);
        $families = $fonts->families();
        $activeFamilies = $families->where('is_active', true)->values();
        $selectedLocaleModel = $selectedLocale !== '' ? $locales->firstWhere('locale', $selectedLocale) : null;
        $resolved = $selectedLocale !== '' ? $resolver->resolve($selectedTheme, $selectedLocale) : $resolver->resolve($selectedTheme);
        $previewLanguage = (string) $request->validated('preview_language', $selectedLocale ?: 'en');
        $previewMode = (string) $request->validated('preview_mode', 'desktop');
        $previewDirection = (string) $request->validated('preview_direction', 'auto');

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
            'localeResolved' => $selectedLocale !== '' ? $resolved : null,
            'preview' => $preview->build($selectedTheme, $selectedLocaleModel ?? $selectedLocale, $previewLanguage, $previewMode, $previewDirection),
            'previewSamples' => $preview->samples(),
            'previewModes' => $preview->modes(),
            'previewDirections' => $preview->directions(),
            'coverageGrid' => $coverage->grid($resolved['stacks'], $selectedLocaleModel ?? $selectedLocale),
            'missingGlyphRisks' => $coverage->missingGlyphRisks($resolved['stacks'], $selectedLocaleModel ?? $selectedLocale),
            'performanceSummary' => $performance->summary($resolved['stacks']),
            'pairingWarnings' => $pairing->warnings($resolved['stacks']),
            'fallbackSimulation' => $fallbacks->simulate($resolved['stacks'], $selectedTheme, $selectedLocale ?: null),
            'readinessCards' => $readiness->languageCards($locales, $selectedTheme),
            'rtlSummary' => $readiness->rtlSummary($locales, $selectedTheme),
            'coverageWarnings' => $this->coverageWarnings($coverage, $families, $selectedLocaleModel),
            'canManageFamilies' => $request->user()?->can('manage font families') ?? false,
            'canManageAssignments' => $request->user()?->can('manage font assignments') ?? false,
            'canViewDiagnostics' => $request->user()?->can('view typography diagnostics') ?? false,
            'canResetLocaleOverride' => $request->user()?->can('reset locale font override') ?? false,
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

    public function resetLocaleOverride(ResetLocaleFontOverrideRequest $request, Locale $locale, ResetLocaleFontOverrideAction $action): RedirectResponse
    {
        $action($locale, $request->user());

        return redirect()
            ->route('admin.typography-center.index', ['locale' => $locale->locale])
            ->with('status', 'Locale font override reset. Theme and global assignments now apply.');
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
