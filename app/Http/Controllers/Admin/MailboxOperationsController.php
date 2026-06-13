<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Mailboxes\CreateMailboxAction;
use App\Actions\Mailboxes\DeleteMessageAction;
use App\Actions\Mailboxes\EmptyMailboxAction;
use App\Actions\Mailboxes\ExpireMailboxAction;
use App\Actions\Mailboxes\LockMailboxAction;
use App\Actions\Mailboxes\MarkMessageReadAction;
use App\Actions\Mailboxes\UnlockMailboxAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Mailboxes\EmptyMailboxRequest;
use App\Http\Requests\Mailboxes\ExpireMailboxRequest;
use App\Http\Requests\Mailboxes\LockMailboxRequest;
use App\Http\Requests\Mailboxes\MailboxFilterRequest;
use App\Http\Requests\Mailboxes\MessageActionRequest;
use App\Http\Requests\Mailboxes\StoreMailboxRequest;
use App\Http\Requests\Mailboxes\UnlockMailboxRequest;
use App\Models\Domain;
use App\Models\Locale;
use App\Models\Mailbox;
use App\Models\MailboxMessage;
use App\Models\User;
use App\Services\Analytics\AnalyticsEventTracker;
use App\Services\Mailboxes\MailboxLifecycleService;
use App\Services\Mailboxes\MailboxMessageService;
use App\Services\Mailboxes\MailboxPrivacyGuard;
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

    public function show(MailboxFilterRequest $request, Mailbox $mailbox, MailboxLifecycleService $lifecycle, MailboxMessageService $messages, AnalyticsEventTracker $analytics): View
    {
        $request->user()?->can('view mailbox') || abort(403);
        $analytics->trackSafely('inbox.viewed', [
            'user' => $mailbox->user_id,
            'locale_id' => $mailbox->locale_id,
            'domain_id' => $mailbox->domain_id,
            'ip' => $request->ip(),
            'metadata' => ['source' => 'admin', 'mailbox_type' => $mailbox->mailbox_type],
        ]);

        return view('dashboard.mailbox-operations.show', [
            'adminUser' => $request->user(), 'mailbox' => $mailbox->load(['domain', 'user', 'locale', 'creator']),
            'timeline' => $lifecycle->timeline($mailbox),
            'messages' => $messages->list($mailbox),
            'canViewMessageContent' => $request->user()?->can('view message content') ?? false,
            'canExpireMailbox' => $request->user()?->can('expire mailbox') ?? false,
            'canLockMailbox' => $request->user()?->can('lock unlock mailbox') ?? false,
            'canEmptyMailbox' => $request->user()?->can('empty mailbox') ?? false,
        ]);
    }

    public function message(MailboxFilterRequest $request, Mailbox $mailbox, MailboxMessage $message, MailboxMessageService $messages, MailboxPrivacyGuard $privacy): View
    {
        abort_unless($messages->belongsTo($mailbox, $message), 404);
        $request->user()?->can('view message content') || abort(403);
        $privacy->recordAccess($request->user(), $message);

        return view('dashboard.mailbox-operations.message', [
            'adminUser' => $request->user(), 'mailbox' => $mailbox->load('domain'), 'message' => $message,
            'canManageMessage' => $request->user()?->can('manage message state') ?? false,
        ]);
    }

    public function expire(ExpireMailboxRequest $request, Mailbox $mailbox, ExpireMailboxAction $action): RedirectResponse
    {
        $action->handle($request->user(), $mailbox);

        return back()->with('status', 'Mailbox expired.');
    }

    public function lock(LockMailboxRequest $request, Mailbox $mailbox, LockMailboxAction $action): RedirectResponse
    {
        $action->handle($request->user(), $mailbox);

        return back()->with('status', 'Mailbox locked.');
    }

    public function unlock(UnlockMailboxRequest $request, Mailbox $mailbox, UnlockMailboxAction $action): RedirectResponse
    {
        $action->handle($request->user(), $mailbox);

        return back()->with('status', 'Mailbox unlocked.');
    }

    public function empty(EmptyMailboxRequest $request, Mailbox $mailbox, EmptyMailboxAction $action): RedirectResponse
    {
        $action->handle($request->user(), $mailbox);

        return back()->with('status', 'Inbox emptied.');
    }

    public function messageAction(MessageActionRequest $request, Mailbox $mailbox, MailboxMessage $message, MailboxMessageService $messages, MarkMessageReadAction $markRead, DeleteMessageAction $delete): RedirectResponse
    {
        abort_unless($messages->belongsTo($mailbox, $message), 404);
        $action = $request->string('action')->toString();

        if ($action === 'delete') {
            $delete->handle($request->user(), $mailbox, $message);

            return redirect()->route('admin.mailbox-operations.show', $mailbox)->with('status', 'Message deleted.');
        }

        $markRead->handle($request->user(), $message, $action === 'read');

        return back()->with('status', $action === 'read' ? 'Message marked read.' : 'Message marked unread.');
    }
}
