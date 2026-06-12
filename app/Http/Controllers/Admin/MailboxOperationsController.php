<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Mailboxes\CreateMailboxAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Mailboxes\MailboxFilterRequest;
use App\Http\Requests\Mailboxes\StoreMailboxRequest;
use App\Models\Domain;
use App\Models\Locale;
use App\Models\Mailbox;
use App\Models\User;
use App\Services\Mailboxes\MailboxLifecycleService;
use App\Services\Mailboxes\MailboxSearchService;
use App\Services\Mailboxes\MailboxStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MailboxOperationsController extends Controller
{
    public function index(MailboxFilterRequest $request, MailboxSearchService $search, MailboxStore $store, MailboxLifecycleService $lifecycle): View
    {
        $filters = [
            'q' => (string) $request->query('q', ''), 'status' => (string) $request->query('status', 'all'),
            'domain_id' => (string) $request->query('domain_id', 'all'), 'owner' => (string) $request->query('owner', 'all'),
            'mailbox_type' => (string) $request->query('mailbox_type', 'all'), 'created' => (string) $request->query('created', 'all'),
            'per_page' => (int) $request->query('per_page', 15),
        ];

        return view('dashboard.mailbox-operations.index', [
            'adminUser' => $request->user(), 'mailboxes' => $search->search([...$request->validated(), ...$filters]),
            'metrics' => $store->metrics(), 'filters' => $filters, 'statuses' => $lifecycle->statuses(), 'types' => $lifecycle->types(),
            'domains' => Domain::query()->orderBy('domain_name')->get(),
            'canCreateMailbox' => $request->user()?->can('create mailbox readiness') ?? false,
        ]);
    }

    public function create(MailboxFilterRequest $request, MailboxLifecycleService $lifecycle): View
    {
        $request->user()?->can('create mailbox readiness') || abort(403);

        return view('dashboard.mailbox-operations.create', [
            'adminUser' => $request->user(),
            'domains' => Domain::query()->where('is_active', true)->where('is_public', true)->where('status', 'ready')->orderBy('domain_name')->get(),
            'users' => User::query()->where('status', 'active')->orderBy('email')->get(['id', 'name', 'email']),
            'locales' => Locale::query()->where('is_active', true)->orderBy('sort_order')->get(), 'types' => $lifecycle->types(),
        ]);
    }

    public function store(StoreMailboxRequest $request, CreateMailboxAction $action): RedirectResponse
    {
        $mailbox = $action->handle($request->user(), $request->validated(), $request->ip());

        return redirect()->route('admin.mailbox-operations.show', $mailbox)->with('status', 'Mailbox created.');
    }

    public function show(MailboxFilterRequest $request, Mailbox $mailbox, MailboxLifecycleService $lifecycle): View
    {
        $request->user()?->can('view mailbox') || abort(403);

        return view('dashboard.mailbox-operations.show', [
            'adminUser' => $request->user(), 'mailbox' => $mailbox->load(['domain', 'user', 'locale', 'creator']),
            'timeline' => $lifecycle->timeline($mailbox),
        ]);
    }
}
