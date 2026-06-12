<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Mail\ActivateInboundConnectionAction;
use App\Actions\Mail\DeactivateInboundConnectionAction;
use App\Actions\Mail\TestInboundMailConnectionAction;
use App\Actions\Mail\UpdateInboundMailConnectionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Mail\InboundMailFilterRequest;
use App\Http\Requests\Mail\StoreInboundMailConnectionRequest;
use App\Http\Requests\Mail\TestInboundMailConnectionRequest;
use App\Http\Requests\Mail\ToggleInboundMailConnectionRequest;
use App\Http\Requests\Mail\UpdateInboundMailConnectionRequest;
use App\Models\Domain;
use App\Models\InboundMailConnection;
use App\Services\Mail\InboundMailConnectionService;
use App\Services\Mail\InboundMailExtensionChecker;
use App\Services\Mail\MailInfrastructureHealthService;
use App\Services\Mail\SmtpConnectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InboundMailConnectionController extends Controller
{
    public function index(
        InboundMailFilterRequest $request,
        InboundMailConnectionService $connections,
        SmtpConnectionService $smtp,
        MailInfrastructureHealthService $health,
        InboundMailExtensionChecker $extensions,
    ): View {
        $filters = [
            'q' => (string) $request->query('q', ''),
            'status' => (string) $request->query('status', 'all'),
            'domain_id' => (string) $request->query('domain_id', 'all'),
            'smtp_q' => (string) $request->query('smtp_q', ''),
            'smtp_status' => (string) $request->query('smtp_status', 'all'),
        ];

        return view('dashboard.imap-smtp.index', [
            'adminUser' => $request->user(),
            'connections' => $connections->search([...$request->validated(), ...$filters]),
            'smtpConnections' => $smtp->search([...$request->validated(), ...$filters]),
            'summary' => $connections->summary(),
            'smtpSummary' => $smtp->summary(),
            'health' => $health->summary(),
            'statuses' => $connections->statuses(),
            'smtpStatuses' => $smtp->statuses(),
            'domains' => Domain::query()->orderBy('domain_name')->get(),
            'filters' => $filters,
            'extension' => $extensions->check(),
            'canManage' => $request->user()?->can('create update inbound connection') ?? false,
            'canTest' => $request->user()?->can('test inbound connection') ?? false,
            'canToggle' => $request->user()?->can('activate deactivate inbound connection') ?? false,
            'canManageSmtp' => $request->user()?->can('create update SMTP connection') ?? false,
            'canTestSmtp' => $request->user()?->can('test SMTP connection') ?? false,
            'canToggleSmtp' => $request->user()?->can('activate deactivate SMTP connection') ?? false,
            'canSetDefaultSmtp' => $request->user()?->can('set default SMTP connection') ?? false,
            'canRunHealthChecks' => $request->user()?->can('run infrastructure health checks') ?? false,
        ]);
    }

    public function create(
        InboundMailFilterRequest $request,
        InboundMailConnectionService $connections,
        InboundMailExtensionChecker $extensions,
    ): View {
        $request->user()?->can('create update inbound connection') || abort(403);

        return view('dashboard.imap-smtp.create', [
            'adminUser' => $request->user(),
            'connection' => null,
            'domains' => Domain::query()->orderBy('domain_name')->get(),
            'encryptionOptions' => $connections->encryptionOptions(),
            'extension' => $extensions->check(),
        ]);
    }

    public function store(StoreInboundMailConnectionRequest $request, UpdateInboundMailConnectionAction $action): RedirectResponse
    {
        $connection = $action->create($request->user(), $request->validated());

        return redirect()->route('admin.imap-smtp.edit', $connection)->with('status', 'Inbound mail connection created.');
    }

    public function edit(
        InboundMailFilterRequest $request,
        InboundMailConnection $inboundMailConnection,
        InboundMailConnectionService $connections,
        InboundMailExtensionChecker $extensions,
    ): View {
        $request->user()?->can('create update inbound connection') || abort(403);

        return view('dashboard.imap-smtp.edit', [
            'adminUser' => $request->user(),
            'connection' => $inboundMailConnection->load('domain'),
            'domains' => Domain::query()->orderBy('domain_name')->get(),
            'encryptionOptions' => $connections->encryptionOptions(),
            'extension' => $extensions->check(),
            'canTest' => $request->user()?->can('test inbound connection') ?? false,
        ]);
    }

    public function update(
        UpdateInboundMailConnectionRequest $request,
        InboundMailConnection $inboundMailConnection,
        UpdateInboundMailConnectionAction $action,
    ): RedirectResponse {
        $connection = $action->update($request->user(), $inboundMailConnection, $request->validated());

        return redirect()->route('admin.imap-smtp.edit', $connection)->with('status', 'Inbound mail connection updated.');
    }

    public function test(
        TestInboundMailConnectionRequest $request,
        InboundMailConnection $inboundMailConnection,
        TestInboundMailConnectionAction $action,
    ): RedirectResponse {
        $connection = $action->handle($request->user(), $inboundMailConnection);

        return redirect()->route('admin.imap-smtp.edit', $connection)->with(
            $connection->status === 'connected' ? 'status' : 'error',
            $connection->last_test_result['message'] ?? 'Connection readiness test completed.',
        );
    }

    public function toggle(
        ToggleInboundMailConnectionRequest $request,
        InboundMailConnection $inboundMailConnection,
        ActivateInboundConnectionAction $activate,
        DeactivateInboundConnectionAction $deactivate,
    ): RedirectResponse {
        $request->validated('status_action') === 'activate'
            ? $activate->handle($request->user(), $inboundMailConnection)
            : $deactivate->handle($request->user(), $inboundMailConnection);

        return redirect()->route('admin.imap-smtp.index')->with('status', 'Inbound connection availability updated.');
    }
}
