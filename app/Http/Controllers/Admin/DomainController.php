<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Domains\ActivateDomainAction;
use App\Actions\Domains\CreateDomainAction;
use App\Actions\Domains\DeactivateDomainAction;
use App\Actions\Domains\RunDomainDnsCheckAction;
use App\Actions\Domains\SetDefaultDomainAction;
use App\Actions\Domains\UpdateDomainAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Domains\DomainFilterRequest;
use App\Http\Requests\Domains\RunDomainDnsCheckRequest;
use App\Http\Requests\Domains\SetDefaultDomainRequest;
use App\Http\Requests\Domains\StoreDomainRequest;
use App\Http\Requests\Domains\UpdateDomainRequest;
use App\Http\Requests\Domains\UpdateDomainStatusRequest;
use App\Models\Domain;
use App\Services\Domains\DomainDnsCheckService;
use App\Services\Domains\DomainReadinessService;
use App\Services\Domains\DomainSearchService;
use App\Services\Domains\DomainStatusResolver;
use App\Services\Domains\DomainStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DomainController extends Controller
{
    public function index(
        DomainFilterRequest $request,
        DomainSearchService $search,
        DomainStore $store,
        DomainReadinessService $readiness,
        DomainStatusResolver $statuses,
    ): View {
        $filters = [
            'q' => (string) $request->query('q', ''),
            'status' => (string) $request->query('status', 'all'),
            'active' => (string) $request->query('active', 'all'),
            'visibility' => (string) $request->query('visibility', 'all'),
            'dns' => (string) $request->query('dns', 'all'),
            'per_page' => (int) $request->query('per_page', 12),
        ];

        return view('dashboard.domains.index', [
            'adminUser' => $request->user(),
            'domains' => $search->search([...$request->validated(), ...$filters]),
            'summary' => $store->summary(),
            'readinessSummary' => $readiness->summary($store->all()),
            'filters' => $filters,
            'statuses' => $statuses->options(),
            'canCreateDomain' => $request->user()?->can('create domain') ?? false,
            'canUpdateDomain' => $request->user()?->can('update domain') ?? false,
            'canChangeStatus' => $request->user()?->can('activate deactivate domain') ?? false,
            'canSetDefault' => $request->user()?->can('set default domain') ?? false,
            'canRunDnsChecks' => $request->user()?->can('run DNS checks') ?? false,
        ]);
    }

    public function create(DomainFilterRequest $request, DomainStatusResolver $statuses, DomainDnsCheckService $dns): View
    {
        $request->user()?->can('create domain') || abort(403);

        return view('dashboard.domains.create', [
            'adminUser' => $request->user(),
            'domain' => null,
            'statuses' => $statuses->options(),
            'expectedRecords' => [],
            'canUpdateDomain' => true,
        ]);
    }

    public function store(StoreDomainRequest $request, CreateDomainAction $action): RedirectResponse
    {
        $domain = $action->handle($request->user(), $request->validated());

        return redirect()->route('admin.domains.edit', $domain)->with('status', 'Domain created.');
    }

    public function edit(DomainFilterRequest $request, Domain $domain, DomainStatusResolver $statuses, DomainDnsCheckService $dns): View
    {
        $request->user()?->can('update domain') || abort(403);

        return view('dashboard.domains.edit', [
            'adminUser' => $request->user(),
            'domain' => $domain,
            'statuses' => $statuses->options(),
            'expectedRecords' => $dns->expectedRecords($domain),
            'canUpdateDomain' => $request->user()?->can('update domain') ?? false,
            'canRunDnsChecks' => $request->user()?->can('run DNS checks') ?? false,
        ]);
    }

    public function update(UpdateDomainRequest $request, Domain $domain, UpdateDomainAction $action): RedirectResponse
    {
        $domain = $action->handle($request->user(), $domain, $request->validated());

        return redirect()->route('admin.domains.edit', $domain)->with('status', 'Domain updated.');
    }

    public function status(UpdateDomainStatusRequest $request, Domain $domain, ActivateDomainAction $activate, DeactivateDomainAction $deactivate): RedirectResponse
    {
        try {
            $request->validated('status_action') === 'activate'
                ? $activate->handle($request->user(), $domain)
                : $deactivate->handle($request->user(), $domain);
        } catch (ValidationException $exception) {
            return redirect()->route('admin.domains.index')->withErrors($exception->errors());
        }

        return redirect()->route('admin.domains.index')->with('status', 'Domain availability updated.');
    }

    public function default(SetDefaultDomainRequest $request, Domain $domain, SetDefaultDomainAction $action): RedirectResponse
    {
        try {
            $action->handle($request->user(), $domain);
        } catch (ValidationException $exception) {
            return redirect()->route('admin.domains.index')->withErrors($exception->errors());
        }

        return redirect()->route('admin.domains.index')->with('status', 'Default domain changed.');
    }

    public function runDnsCheck(RunDomainDnsCheckRequest $request, Domain $domain, RunDomainDnsCheckAction $action): RedirectResponse
    {
        $action->handle($request->user(), $domain);

        return redirect()->route('admin.domains.edit', $domain)->with('status', 'DNS readiness check completed.');
    }
}
