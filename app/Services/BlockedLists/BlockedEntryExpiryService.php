<?php

namespace App\Services\BlockedLists;

use App\Models\BlockedListEntry;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Notifications\NotificationService;

class BlockedEntryExpiryService
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly BlockedListCacheService $cache,
        private readonly NotificationService $notifications,
    ) {}

    public function expire(?User $actor = null): int
    {
        $entries = BlockedListEntry::query()
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        if ($entries->isEmpty()) {
            return 0;
        }

        BlockedListEntry::query()->whereKey($entries->modelKeys())->update([
            'status' => 'expired',
            'updated_at' => now(),
            'updated_by' => $actor?->id,
        ]);

        $this->cache->invalidate();
        $count = $entries->count();

        $this->audit->record('blocked_lists.entries_expired', $actor, null, [
            'count' => $count,
            'entry_ids' => $entries->modelKeys(),
        ], ['module' => 'mail-infrastructure', 'severity' => 'info']);

        if ($count >= 25) {
            $this->notifications->dispatch([
                'event_key' => 'blocked_list_large_change',
                'type' => 'mail-infrastructure',
                'severity' => 'warning',
                'title' => 'Large blocked-list expiration completed',
                'message' => $count.' blocked-list entries expired automatically.',
                'related_module' => 'mail-infrastructure',
                'action_route' => 'admin.blocked-lists.index',
                'action_parameters' => ['expiry' => 'expired'],
            ], sendEmail: false);
        }

        return $count;
    }
}
