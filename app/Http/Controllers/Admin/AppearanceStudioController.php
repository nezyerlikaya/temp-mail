<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Appearance\ResetAppearanceAction;
use App\Actions\Appearance\ResetAppearanceTokenAction;
use App\Actions\Appearance\UpdateAppearanceDraftAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Appearance\ResetAppearanceRequest;
use App\Http\Requests\Appearance\ResetAppearanceTokenRequest;
use App\Http\Requests\Appearance\UpdateAppearanceRequest;
use App\Services\Appearance\AppearanceCssVariableResolver;
use App\Services\Appearance\AppearanceSettingsStore;
use App\Services\Appearance\AppearanceTokenRegistry;
use App\Services\Themes\ThemeManager;
use App\Services\Themes\ThemeRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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
    ): View {
        Gate::authorize('view appearance');

        $selectedTheme = in_array($request->query('theme'), $themes->slugs(), true)
            ? (string) $request->query('theme')
            : $themeManager->active()->slug;
        $setting = $store->setting($selectedTheme);
        $draftTokens = $store->draftTokens($selectedTheme);
        $defaultTokens = $tokens->defaultFor($selectedTheme);

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
            'radiusOptions' => $tokens->radiusOptions(),
            'shadowOptions' => $tokens->shadowOptions(),
            'motionOptions' => $tokens->motionOptions(),
            'canUpdateAppearance' => $request->user()?->can('update appearance') ?? false,
            'canResetAppearance' => $request->user()?->can('reset appearance') ?? false,
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
}
