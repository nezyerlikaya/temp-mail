<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Appearance\PublishAppearanceAction;
use App\Actions\Appearance\ResetAppearanceAction;
use App\Actions\Appearance\ResetAppearanceTokenAction;
use App\Actions\Appearance\RollbackAppearanceAction;
use App\Actions\Appearance\UpdateAppearanceDraftAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Appearance\PreviewAppearanceRequest;
use App\Http\Requests\Appearance\PublishAppearanceRequest;
use App\Http\Requests\Appearance\ResetAppearanceRequest;
use App\Http\Requests\Appearance\ResetAppearanceTokenRequest;
use App\Http\Requests\Appearance\RollbackAppearanceRequest;
use App\Http\Requests\Appearance\UpdateAppearanceRequest;
use App\Models\AppearanceVersion;
use App\Services\Appearance\AppearanceContrastService;
use App\Services\Appearance\AppearanceCssVariableResolver;
use App\Services\Appearance\AppearancePaletteService;
use App\Services\Appearance\AppearancePreviewService;
use App\Services\Appearance\AppearanceSettingsStore;
use App\Services\Appearance\AppearanceTokenRegistry;
use App\Services\Appearance\AppearanceVersionService;
use App\Services\Themes\ThemeManager;
use App\Services\Themes\ThemeRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use InvalidArgumentException;

class AppearanceStudioController extends Controller
{
    public function index(
        Request $request,
        ThemeRegistry $themes,
        ThemeManager $themeManager,
        AppearanceTokenRegistry $tokens,
        AppearanceSettingsStore $store,
        AppearanceCssVariableResolver $css,
        AppearancePreviewService $preview,
        AppearanceContrastService $contrast,
        AppearancePaletteService $palette,
        AppearanceVersionService $versions,
    ): View {
        Gate::authorize('view appearance');

        $selectedTheme = in_array($request->query('theme'), $themes->slugs(), true)
            ? (string) $request->query('theme')
            : $themeManager->active()->slug;
        $setting = $store->setting($selectedTheme);
        $draftTokens = $store->draftTokens($selectedTheme);
        $defaultTokens = $tokens->defaultFor($selectedTheme);
        $previewData = $preview->build($selectedTheme, $draftTokens);

        return view('dashboard.appearance-studio.index', [
            'adminUser' => $request->user(),
            'themes' => $themeManager->cards(),
            'selectedTheme' => $selectedTheme,
            'activeTheme' => $themeManager->active()->slug,
            'setting' => $setting,
            'tokenDefinitions' => $tokens->tokens(),
            'defaultTokens' => $defaultTokens,
            'draftTokens' => $draftTokens,
            'cssVariables' => $css->variables($draftTokens),
            'preview' => $previewData,
            'contrastReport' => $contrast->report($draftTokens),
            'paletteSuggestions' => $palette->suggestions($draftTokens['brand_color']),
            'versions' => $versions->history($selectedTheme),
            'signedPreviewUrl' => URL::signedRoute('admin.appearance-studio.preview', ['theme' => $selectedTheme]),
            'radiusOptions' => $tokens->radiusOptions(),
            'shadowOptions' => $tokens->shadowOptions(),
            'motionOptions' => $tokens->motionOptions(),
            'canUpdateAppearance' => $request->user()?->can('update appearance') ?? false,
            'canResetAppearance' => $request->user()?->can('reset appearance') ?? false,
            'canPreviewAppearance' => $request->user()?->can('preview appearance') ?? false,
            'canPublishAppearance' => $request->user()?->can('publish appearance') ?? false,
            'canRollbackAppearance' => $request->user()?->can('rollback appearance') ?? false,
        ]);
    }

    public function preview(
        PreviewAppearanceRequest $request,
        AppearanceSettingsStore $store,
        AppearancePreviewService $preview,
        AppearanceTokenRegistry $tokens,
    ): View {
        $theme = (string) $request->validated('theme');

        return view('dashboard.appearance-studio.preview', [
            'preview' => $preview->build($theme, $store->draftTokens($theme)),
            'radiusOptions' => $tokens->radiusOptions(),
            'shadowOptions' => $tokens->shadowOptions(),
            'motionOptions' => $tokens->motionOptions(),
            'selectedMode' => $request->validated('mode', 'homepage'),
            'selectedDevice' => $request->validated('device', 'desktop'),
            'selectedDirection' => $request->validated('direction', 'ltr'),
        ]);
    }

    public function update(UpdateAppearanceRequest $request, UpdateAppearanceDraftAction $action): RedirectResponse
    {
        try {
            $action->handle($request->user(), (string) $request->validated('theme'), $request->validated());
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->withErrors(['tokens' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.appearance-studio.index', ['theme' => $request->validated('theme')])
            ->with('status', 'Appearance draft tokens saved.');
    }

    public function reset(ResetAppearanceRequest $request, ResetAppearanceAction $action): RedirectResponse
    {
        $theme = (string) $request->validated('theme');
        $action->handle($request->user(), $theme);

        return redirect()
            ->route('admin.appearance-studio.index', ['theme' => $theme])
            ->with('status', 'Appearance reset to selected theme defaults.');
    }

    public function resetToken(ResetAppearanceTokenRequest $request, ResetAppearanceTokenAction $action): RedirectResponse
    {
        $theme = (string) $request->validated('theme');
        $action->handle($request->user(), $theme, (string) $request->validated('token'));

        return redirect()
            ->route('admin.appearance-studio.index', ['theme' => $theme])
            ->with('status', 'Appearance token reset to the theme default.');
    }

    public function publish(PublishAppearanceRequest $request, PublishAppearanceAction $action): RedirectResponse
    {
        $theme = (string) $request->validated('theme');

        try {
            $version = $action->handle($request->user(), $theme);
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->withErrors(['publish' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.appearance-studio.index', ['theme' => $theme])
            ->with('status', 'Appearance published as version '.$version->version_number.'.');
    }

    public function rollback(RollbackAppearanceRequest $request, RollbackAppearanceAction $action): RedirectResponse
    {
        $theme = (string) $request->validated('theme');
        $version = AppearanceVersion::query()->findOrFail($request->validated('version_id'));

        try {
            $restored = $action->handle($request->user(), $theme, $version);
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->withErrors(['rollback' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.appearance-studio.index', ['theme' => $theme])
            ->with('status', 'Appearance rolled back as version '.$restored->version_number.'.');
    }
}
