<?php

namespace App\Http\Controllers;

use App\Actions\Installer\CompleteInstallation;
use App\Actions\Installer\SaveDatabaseConfiguration;
use App\Actions\Installer\TestDatabaseConnection;
use App\Http\Requests\Installer\AdminAccountRequest;
use App\Http\Requests\Installer\DatabaseSetupRequest;
use App\Services\Installer\DatabaseReadiness;
use App\Services\Installer\EnvironmentManager;
use App\Services\Installer\InstallState;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class InstallerController extends Controller
{
    public function __construct(
        private readonly EnvironmentManager $environment,
        private readonly InstallState $installState,
        private readonly DatabaseReadiness $readiness,
    ) {}

    public function readiness(): RedirectResponse|View
    {
        $this->environment->ensureEnvironmentFile();

        if ($this->installState->isInstalled()) {
            return redirect()->route('login');
        }

        return view('installer.readiness', [
            'checklist' => $this->readiness->checklist(),
            'connections' => $this->readiness->connections(),
        ]);
    }

    public function database(): RedirectResponse|View
    {
        $this->environment->ensureEnvironmentFile();

        if ($this->installState->isInstalled()) {
            return redirect()->route('login');
        }

        return view('installer.database', [
            'connections' => $this->readiness->connections(),
        ]);
    }

    public function storeDatabase(
        DatabaseSetupRequest $request,
        TestDatabaseConnection $testConnection,
        SaveDatabaseConfiguration $saveDatabaseConfiguration,
    ): RedirectResponse {
        if ($this->installState->isInstalled()) {
            return redirect()->route('login');
        }

        $credentials = $request->validated();

        try {
            $testConnection->handle($credentials);
            $saveDatabaseConfiguration->handle($credentials);
        } catch (RuntimeException $exception) {
            return back()
                ->withInput($request->except('password'))
                ->withErrors(['database' => $exception->getMessage()]);
        }

        $request->session()->put('installer.database_ready', true);

        return redirect()->route('install.admin')->with('status', 'Database connection verified.');
    }

    public function admin(): RedirectResponse|View
    {
        $this->environment->ensureEnvironmentFile();

        if ($this->installState->isInstalled()) {
            return redirect()->route('login');
        }

        if (! session('installer.database_ready')) {
            return redirect()->route('install.database');
        }

        return view('installer.admin');
    }

    public function storeAdmin(AdminAccountRequest $request, CompleteInstallation $completeInstallation): RedirectResponse
    {
        if ($this->installState->isInstalled()) {
            return redirect()->route('login');
        }

        if (! session('installer.database_ready')) {
            return redirect()->route('install.database');
        }

        try {
            $completeInstallation->handle($request->safe()->only(['name', 'email', 'password']));
        } catch (Throwable) {
            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors(['install' => 'Installation could not finish. Nothing was locked. Review the database details and try again.']);
        }

        $request->session()->forget('installer.database_ready');

        return redirect()->route('login')->with('status', 'Installation complete. Sign in with your admin account.');
    }
}
