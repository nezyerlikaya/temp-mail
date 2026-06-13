<?php

namespace App\Http\Controllers\Admin;

use App\Actions\BlockedLists\ActivateBlockedEntryAction;
use App\Actions\BlockedLists\CreateBlockedEntryAction;
use App\Actions\BlockedLists\DeactivateBlockedEntryAction;
use App\Actions\BlockedLists\UpdateBlockedEntryAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\BlockedLists\BlockedListFilterRequest;
use App\Http\Requests\BlockedLists\StoreBlockedEntryRequest;
use App\Http\Requests\BlockedLists\ToggleBlockedEntryRequest;
use App\Http\Requests\BlockedLists\UpdateBlockedEntryRequest;
use App\Models\BlockedListEntry;
use App\Models\User;
use App\Services\BlockedLists\BlockedListSearchService;
use App\Services\BlockedLists\BlockedListService;
use App\Services\BlockedLists\BlockedValueNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BlockedListController extends Controller
{
    public function index(
        BlockedListFilterRequest $request,
        BlockedListSearchService $search,
        BlockedListService $service,
        BlockedValueNormalizer $normalizer,
    ): View {
        $filters = $request->filters();
        $editEntry = filled($request->query('edit')) ? BlockedListEntry::query()->find($request->integer('edit')) : null;

        return view('dashboard.blocked-lists.index', [
            'entries' => $search->search($filters),
            'summary' => $search->summary(),
            'filters' => $filters,
            'groups' => $service->groups(),
            'types' => $normalizer->types(),
            'statuses' => $service->statuses(),
            'sources' => $service->sources(),
            'notificationReadiness' => $service->notificationReadiness(),
            'administrators' => User::query()->where('status', 'active')->whereIn('role', ['owner', 'admin', 'moderator'])->orderBy('name')->get(['id', 'name', 'role']),
            'editEntry' => $editEntry,
            'canCreate' => $request->user()?->can('create blocked entry') ?? false,
            'canUpdate' => $request->user()?->can('update blocked entry') ?? false,
            'canToggle' => $request->user()?->can('activate deactivate blocked entry') ?? false,
            'canViewSensitiveIp' => $request->user()?->can('view sensitive blocked list values') ?? false,
            'canViewRelatedAbuseCase' => $request->user()?->can('view related blocked list abuse case') ?? false,
        ]);
    }

    public function store(StoreBlockedEntryRequest $request, CreateBlockedEntryAction $action): RedirectResponse
    {
        $action->handle($request->user(), $request->validated());

        return redirect()->route('admin.blocked-lists.index', ['group' => $this->groupFor($request->validated('entry_type'))])->with('status', 'Blocked-list entry created.');
    }

    public function update(UpdateBlockedEntryRequest $request, BlockedListEntry $blockedListEntry, UpdateBlockedEntryAction $action): RedirectResponse
    {
        $action->handle($request->user(), $blockedListEntry, $request->validated());

        return redirect()->route('admin.blocked-lists.index', ['group' => $this->groupFor($request->validated('entry_type'))])->with('status', 'Blocked-list entry updated.');
    }

    public function activate(ToggleBlockedEntryRequest $request, BlockedListEntry $blockedListEntry, ActivateBlockedEntryAction $action): RedirectResponse
    {
        $action->handle($request->user(), $blockedListEntry);

        return back()->with('status', 'Blocked-list entry activated.');
    }

    public function deactivate(ToggleBlockedEntryRequest $request, BlockedListEntry $blockedListEntry, DeactivateBlockedEntryAction $action): RedirectResponse
    {
        $action->handle($request->user(), $blockedListEntry);

        return back()->with('status', 'Blocked-list entry deactivated.');
    }

    private function groupFor(string $type): string
    {
        return match ($type) {
            'sender_email', 'sender_domain' => 'senders',
            'recipient_email_pattern', 'recipient_domain' => 'recipients',
            'ip_address' => 'ip-rules',
            'comment_email', 'blocked_phrase' => 'comment-rules',
            default => 'domains',
        };
    }
}
