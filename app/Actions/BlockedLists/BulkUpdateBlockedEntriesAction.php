<?php

namespace App\Actions\BlockedLists;

use App\Models\BlockedListEntry;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\BlockedLists\BlockedListCacheService;
use App\Services\Notifications\NotificationService;
use Illuminate\Support\Carbon;

class BulkUpdateBlockedEntriesAction
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly BlockedListCacheService $cache,
        private readonly NotificationService $notifications,
    ) {}

    /** @param array<string, mixed> $payload */
    public function handle(User $actor, array $payload): int
    {
        $ids = collect($payload['entry_ids'] ?? [])->map(fn ($id): int => (int) $id)->filter()->unique()->values();
        $action = (string) $payload['bulk_action'];

        $updates = match ($action) {
            'activate' => ['status' => 'active'],
            'deactivate' => ['status' => 'inactive'],
            'expire' => ['status' => 'expired', 'expires_at' => now()],
            'update_expiration' => ['expires_at' => Carbon::parse((string) $payload['expires_at'])->endOfDay()],
            default => [],
        };

        $count = BlockedListEntry::query()
            ->whereKey($ids->all())
            ->update([...$updates, 'updated_by' => $actor->id, 'updated_at' => now()]);

        $this->cache->invalidate();
        $this->audit->record('blocked_lists.bulk_updated', $actor, null, [
            'count' => $count,
            'bulk_action' => $action,
            'entry_ids' => $ids->all(),
        ], ['module' => 'mail-infrastructure']);

        if ($count >= 25) {
            $this->notifications->dispatch([
                'event_key' => 'blocked_list_large_change',
                'type' => 'mail-infrastructure',
                'severity' => 'warning',
                'title' => 'Large blocked-list change',
                'message' => $count.' blocked-list entries were changed in one bulk action.',
                'related_module' => 'mail-infrastructure',
                'action_route' => 'admin.blocked-lists.index',
            ], sendEmail: false);
        }

        return $count;
    }
}
