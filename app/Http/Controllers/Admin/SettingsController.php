<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Settings\UpdateSettingsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateBrandSettingsRequest;
use App\Http\Requests\Settings\UpdateGeneralSettingsRequest;
use App\Http\Requests\Settings\UpdateLegalSettingsRequest;
use App\Http\Requests\Settings\UpdateLocalizationDefaultsRequest;
use App\Http\Requests\Settings\UpdateMaintenanceSettingsRequest;
use App\Services\Settings\BrandAssetResolver;
use App\Services\Settings\LegalPageResolver;
use App\Services\Settings\SettingsResolver;
use App\Services\Settings\SystemReadinessService;
use DateTimeZone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(
        Request $request,
        SettingsResolver $settings,
        BrandAssetResolver $brandAssets,
        LegalPageResolver $legalPages,
        SystemReadinessService $readiness,
    ): View {
        Gate::authorize('admin.settings.view');

        return view('dashboard.settings.index', [
            'adminUser' => $request->user(),
            'settings' => $settings->all(),
            'activeGroup' => in_array($request->query('group'), $this->groups(), true) ? $request->query('group') : 'general',
            'languages' => $settings->activeLanguages(),
            'timezones' => DateTimeZone::listIdentifiers(),
            'brandAssets' => $brandAssets->assets(),
            'legalPages' => $legalPages->pages(),
            'systemStatuses' => $readiness->statuses(),
        ]);
    }

    public function updateGeneral(UpdateGeneralSettingsRequest $request, UpdateSettingsAction $update): RedirectResponse
    {
        return $this->update($request, $update, 'general');
    }

    public function updateBrand(UpdateBrandSettingsRequest $request, UpdateSettingsAction $update): RedirectResponse
    {
        return $this->update($request, $update, 'brand');
    }

    public function updateLocalization(UpdateLocalizationDefaultsRequest $request, UpdateSettingsAction $update): RedirectResponse
    {
        return $this->update($request, $update, 'localization');
    }

    public function updateMaintenance(UpdateMaintenanceSettingsRequest $request, UpdateSettingsAction $update): RedirectResponse
    {
        return $this->update($request, $update, 'maintenance');
    }

    public function updateLegal(UpdateLegalSettingsRequest $request, UpdateSettingsAction $update): RedirectResponse
    {
        return $this->update($request, $update, 'legal');
    }

    public function reset(Request $request, UpdateSettingsAction $update, string $group): RedirectResponse
    {
        Gate::authorize('admin.settings.manage');
        abort_unless(in_array($group, $this->groups(), true) && $group !== 'system', 404);

        $update->reset($request->user(), $group);

        return redirect()->route('admin.settings.index', ['group' => $group])->with('status', str($group)->headline().' settings reset to defaults.');
    }

    private function update(FormRequest $request, UpdateSettingsAction $update, string $group): RedirectResponse
    {
        $update->handle($request->user(), $group, $request->validated());

        return redirect()->route('admin.settings.index', ['group' => $group])->with('status', str($group)->headline().' settings saved.');
    }

    /** @return array<int, string> */
    private function groups(): array
    {
        return ['general', 'brand', 'localization', 'maintenance', 'legal', 'system'];
    }
}
