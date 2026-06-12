<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Mail\ActivateSmtpConnectionAction;
use App\Actions\Mail\DeactivateSmtpConnectionAction;
use App\Actions\Mail\RunMailInfrastructureChecksAction;
use App\Actions\Mail\SendSmtpTestEmailAction;
use App\Actions\Mail\SetDefaultSmtpConnectionAction;
use App\Actions\Mail\TestSmtpConnectionAction;
use App\Actions\Mail\UpdateSmtpConnectionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Mail\InboundMailFilterRequest;
use App\Http\Requests\Mail\RunMailInfrastructureChecksRequest;
use App\Http\Requests\Mail\SendSmtpTestEmailRequest;
use App\Http\Requests\Mail\SetDefaultSmtpConnectionRequest;
use App\Http\Requests\Mail\StoreSmtpConnectionRequest;
use App\Http\Requests\Mail\TestSmtpConnectionRequest;
use App\Http\Requests\Mail\ToggleSmtpConnectionRequest;
use App\Http\Requests\Mail\UpdateSmtpConnectionRequest;
use App\Models\Domain;
use App\Models\SmtpConnection;
use App\Services\Mail\SmtpConnectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SmtpConnectionController extends Controller
{
    public function create(InboundMailFilterRequest $request, SmtpConnectionService $smtp): View
    {
        $request->user()?->can('create update SMTP connection') || abort(403);

        return view('dashboard.imap-smtp.smtp-create', [
            'adminUser' => $request->user(),
            'connection' => null,
            'domains' => Domain::query()->orderBy('domain_name')->get(),
            'encryptionOptions' => $smtp->encryptionOptions(),
        ]);
    }

    public function store(StoreSmtpConnectionRequest $request, UpdateSmtpConnectionAction $action): RedirectResponse
    {
        $connection = $action->create($request->user(), $request->validated());

        return redirect()->route('admin.imap-smtp.smtp.edit', $connection)->with('status', 'SMTP connection created.');
    }

    public function edit(InboundMailFilterRequest $request, SmtpConnection $smtpConnection, SmtpConnectionService $smtp): View
    {
        $request->user()?->can('create update SMTP connection') || abort(403);

        return view('dashboard.imap-smtp.smtp-edit', [
            'adminUser' => $request->user(),
            'connection' => $smtpConnection->load('domain'),
            'domains' => Domain::query()->orderBy('domain_name')->get(),
            'encryptionOptions' => $smtp->encryptionOptions(),
            'canTest' => $request->user()?->can('test SMTP connection') ?? false,
            'canSendTest' => $request->user()?->can('send SMTP test email') ?? false,
        ]);
    }

    public function update(UpdateSmtpConnectionRequest $request, SmtpConnection $smtpConnection, UpdateSmtpConnectionAction $action): RedirectResponse
    {
        $connection = $action->update($request->user(), $smtpConnection, $request->validated());

        return redirect()->route('admin.imap-smtp.smtp.edit', $connection)->with('status', 'SMTP connection updated.');
    }

    public function test(TestSmtpConnectionRequest $request, SmtpConnection $smtpConnection, TestSmtpConnectionAction $action): RedirectResponse
    {
        $connection = $action->handle($request->user(), $smtpConnection);

        return redirect()->route('admin.imap-smtp.smtp.edit', $connection)->with(
            $connection->status === 'connected' ? 'status' : 'error',
            $connection->last_test_result['message'] ?? 'SMTP readiness test completed.',
        );
    }

    public function sendTest(SendSmtpTestEmailRequest $request, SmtpConnection $smtpConnection, SendSmtpTestEmailAction $action): RedirectResponse
    {
        $result = $action->handle($request->user(), $smtpConnection, (string) $request->validated('recipient'));

        return redirect()->route('admin.imap-smtp.smtp.edit', $smtpConnection)->with(
            $result['status'] === 'sent' ? 'status' : 'error',
            $result['message'],
        );
    }

    public function default(SetDefaultSmtpConnectionRequest $request, SmtpConnection $smtpConnection, SetDefaultSmtpConnectionAction $action): RedirectResponse
    {
        try {
            $action->handle($request->user(), $smtpConnection);
        } catch (ValidationException $exception) {
            return redirect()->route('admin.imap-smtp.index')->withErrors($exception->errors());
        }

        return redirect()->route('admin.imap-smtp.index')->with('status', 'Default SMTP connection changed.');
    }

    public function toggle(
        ToggleSmtpConnectionRequest $request,
        SmtpConnection $smtpConnection,
        ActivateSmtpConnectionAction $activate,
        DeactivateSmtpConnectionAction $deactivate,
    ): RedirectResponse {
        try {
            $request->validated('status_action') === 'activate'
                ? $activate->handle($request->user(), $smtpConnection)
                : $deactivate->handle($request->user(), $smtpConnection);
        } catch (ValidationException $exception) {
            return redirect()->route('admin.imap-smtp.index')->withErrors($exception->errors());
        }

        return redirect()->route('admin.imap-smtp.index')->with('status', 'SMTP connection availability updated.');
    }

    public function runAll(RunMailInfrastructureChecksRequest $request, RunMailInfrastructureChecksAction $action): RedirectResponse
    {
        $summary = $action->handle($request->user());

        return redirect()->route('admin.imap-smtp.index')->with(
            $summary['overall'] === 'healthy' ? 'status' : 'error',
            'Mail infrastructure checks completed with '.$summary['overall'].' status.',
        );
    }
}
