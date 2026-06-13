<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Themes\ThemeActivationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Themes\ActivateThemeRequest;
use App\Services\Themes\ThemeActivationLock;
use App\Services\Themes\ThemeManager;
use App\Services\Themes\ThemeRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;
use RuntimeException;

class ThemeLaunchController extends Controller
{
    public function index(
        Request $request,
        ThemeManager $themes,
        ThemeRegistry $registry,
        ThemeActivationLock $locks,
    ): View {
        $request->user()?->can('admin.theme-launch-center.view') || abort(403);

        return view('dashboard.theme-launch-center.index', [
            'adminUser' => $request->user(),
            'themes' => $themes->cards(),
            'registeredThemeCount' => count($registry->all()),
            'lockStatus' => $locks->status(),
            'rollbackReadiness' => $themes->rollbackReadiness(),
            'canActivateThemes' => $request->user()?->can('activate theme') ?? false,
        ]);
    }

    public function activate(ActivateThemeRequest $request, ThemeActivationAction $action): RedirectResponse
    {
        try {
            $theme = $action->handle($request->user(), (string) $request->validated('theme'));
        } catch (InvalidArgumentException|RuntimeException $exception) {
            return back()
                ->withInput()
                ->withErrors(['theme' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.theme-launch-center.index')
            ->with('status', str($theme->slug)->headline().' is now active for the public website.');
    }
}
