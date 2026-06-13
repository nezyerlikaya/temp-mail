<?php

namespace App\Services\BlockedLists;

use App\Models\BlockedListEntry;
use Illuminate\Support\Facades\Cache;

class BlockedListCacheService
{
    private const KEY = 'blocked_lists.active_rules.v1';

    /** @return array<int, array<string, mixed>> */
    public function activeRules(): array
    {
        return Cache::remember(self::KEY, now()->addMinutes(10), fn (): array => BlockedListEntry::query()
            ->where('status', 'active')
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->get()
            ->map(fn (BlockedListEntry $entry): array => $this->serialize($entry))
            ->all());
    }

    public function invalidate(): void
    {
        Cache::forget(self::KEY);
    }

    /** @return array<string, mixed> */
    public function serialize(BlockedListEntry $entry): array
    {
        return [
            'id' => $entry->id,
            'entry_type' => $entry->entry_type,
            'normalized_hash' => $entry->normalized_hash,
            'normalized_value' => $entry->encrypted_normalized_value,
            'display_value' => $entry->display_value,
            'reason' => $entry->reason,
            'source' => $entry->source,
            'status' => $entry->status,
            'starts_at' => $entry->starts_at,
            'expires_at' => $entry->expires_at,
        ];
    }
}
