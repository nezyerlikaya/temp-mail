<?php

namespace App\Http\Controllers\Admin;

use App\Actions\BlockedLists\ActivateBlockedEntryAction;
use App\Actions\BlockedLists\BulkUpdateBlockedEntriesAction;
use App\Actions\BlockedLists\CreateBlockedEntryAction;
use App\Actions\BlockedLists\DeactivateBlockedEntryAction;
use App\Actions\BlockedLists\ExpireBlockedEntriesAction;
use App\Actions\BlockedLists\ExportBlockedEntriesAction;
use App\Actions\BlockedLists\ImportBlockedEntriesAction;
use App\Actions\BlockedLists\UpdateBlockedEntryAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\BlockedLists\BlockedListFilterRequest;
use App\Http\Requests\BlockedLists\BulkUpdateBlockedEntriesRequest;
use App\Http\Requests\BlockedLists\ExportBlockedEntriesRequest;
use App\Http\Requests\BlockedLists\ImportBlockedEntriesRequest;
use App\Http\Requests\BlockedLists\StoreBlockedEntryRequest;
use App\Http\Requests\BlockedLists\TestBlockedEntryRequest;
use App\Http\Requests\BlockedLists\ToggleBlockedEntryRequest;
use App\Http\Requests\BlockedLists\UpdateBlockedEntryRequest;
use App\Models\BlockedListEntry;
use App\Models\User;
use App\Services\BlockedLists\BlockedListMatcher;
use App\Services\BlockedLists\BlockedListSearchService;
use App\Services\BlockedLists\BlockedListService;
use App\Services\BlockedLists\BlockedValueNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
            'canBulkModify' => $request->user()?->can('bulk modify blocked entries') ?? false,
            'canImport' => $request->user()?->can('import blocked entries') ?? false,
            'canExport' => $request->user()?->can('export blocked entries') ?? false,
            'canRunEnforcementTest' => $request->user()?->can('run enforcement test') ?? false,
            'canViewSensitiveIp' => $request->user()?->can('view sensitive blocked list values') ?? false,
            'canViewRelatedAbuseCase' => $request->user()?->can('view related blocked list abuse case') ?? false,
            'importPreview' => session('blocked_import_preview'),
            'matchResult' => session('blocked_match_result'),
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

    public function test(TestBlockedEntryRequest $request, BlockedListMatcher $matcher): RedirectResponse
    {
        $result = $matcher->match($request->validated('entry_type'), $request->validated('value'));

        return back()->with('blocked_match_result', $result);
    }

    public function import(ImportBlockedEntriesRequest $request, ImportBlockedEntriesAction $action): RedirectResponse
    {
        if ($request->validated('mode') === 'preview') {
            return back()
                ->withInput()
                ->with('blocked_import_preview', $action->preview($request->validated('csv')))
                ->with('status', 'CSV preview completed. Review every row before import.');
        }

        $result = $action->handle($request->user(), $request->validated('csv'));

        return back()
            ->with('blocked_import_preview', $result['preview'])
            ->with('status', $result['created'].' blocked-list entries imported transactionally.');
    }

    public function export(ExportBlockedEntriesRequest $request, ExportBlockedEntriesAction $action): StreamedResponse
    {
        return $action->handle(
            $request->user(),
            $request->filters(),
            $request->boolean('include_sensitive_ip') && ($request->user()?->can('view sensitive blocked list values') ?? false),
        );
    }

    public function bulk(BulkUpdateBlockedEntriesRequest $request, BulkUpdateBlockedEntriesAction $action): RedirectResponse
    {
        $count = $action->handle($request->user(), $request->validated());

        return back()->with('status', $count.' blocked-list entries updated.');
    }

    public function expire(ToggleBlockedEntryRequest $request, ExpireBlockedEntriesAction $action): RedirectResponse
    {
        $count = $action->handle($request->user());

        return back()->with('status', $count.' expired blocked-list entries processed.');
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
