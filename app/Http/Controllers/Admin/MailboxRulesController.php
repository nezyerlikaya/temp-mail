<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Mailboxes\ExpiredMailboxCleanupAction;
use App\Actions\Mailboxes\RunMailboxHealthCheckAction;
use App\Actions\Mailboxes\UpdateMailboxRulesAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Mailboxes\RunMailboxCleanupRequest;
use App\Http\Requests\Mailboxes\RunMailboxHealthCheckRequest;
use App\Http\Requests\Mailboxes\UpdateMailboxRulesRequest;
use App\Services\Mailboxes\MailboxDeliveryHealthService;
use App\Services\Mailboxes\MailboxRetentionService;
use App\Services\Mailboxes\MailboxRulesStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MailboxRulesController extends Controller
{
    public function index(Request $request, MailboxRulesStore $store, MailboxRetentionService $retention, MailboxDeliveryHealthService $health): View
    {
        $rules = $store->current();
        $latest = $health->latest();

        return view('dashboard.mailbox-rules.index', [
            'adminUser' => $request->user(), 'rules' => $rules, 'retention' => $retention->preview($rules),
            'health' => $latest?->summary ?? $health->summary(), 'latestHealth' => $latest, 'healthHistory' => $health->history(),
            'canUpdate' => $request->user()?->can('update mailbox rules') ?? false,
            'canCleanup' => $request->user()?->can('run mailbox cleanup') ?? false,
            'canRunHealth' => $request->user()?->can('run mailbox delivery health checks') ?? false,
        ]);
    }

    public function update(UpdateMailboxRulesRequest $request, UpdateMailboxRulesAction $action): RedirectResponse
    {
        $action->handle($request->user(), $request->validated());

        return back()->with('status', 'Mailbox rules saved.');
    }

    public function cleanup(RunMailboxCleanupRequest $request, ExpiredMailboxCleanupAction $action): RedirectResponse
    {
        $result = $action->handle($request->user());

        return back()->with('status', $result['removed'].' expired mailbox(es) removed.');
    }

    public function health(RunMailboxHealthCheckRequest $request, RunMailboxHealthCheckAction $action): RedirectResponse
    {
        $action->handle($request->user());

        return back()->with('status', 'Mailbox delivery health checked.');
    }
}
