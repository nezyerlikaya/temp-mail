<?php

namespace App\Services\BlockedLists;

use App\Models\BlockedListEntry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class BlockedListSearchService
{
    /** @param array<string, mixed> $filters */
    public function search(array $filters): LengthAwarePaginator
    {
        return BlockedListEntry::query()
            ->with(['creator', 'abuseReport'])
            ->when(($filters['group'] ?? 'senders') !== 'all', fn (Builder $query) => $query->whereIn('entry_type', $this->typesForGroup((string) $filters['group'])))
            ->when(($filters['entry_type'] ?? 'all') !== 'all', fn (Builder $query) => $query->where('entry_type', $filters['entry_type']))
            ->when(($filters['status'] ?? 'all') !== 'all', fn (Builder $query) => $query->where('status', $filters['status']))
            ->when(($filters['source'] ?? 'all') !== 'all', fn (Builder $query) => $query->where('source', $filters['source']))
            ->when(($filters['expiry'] ?? 'all') === 'expires', fn (Builder $query) => $query->whereNotNull('expires_at')->where('expires_at', '>', now()))
            ->when(($filters['expiry'] ?? 'all') === 'expired', fn (Builder $query) => $query->whereNotNull('expires_at')->where('expires_at', '<=', now()))
            ->when(filled($filters['created_by'] ?? null), fn (Builder $query) => $query->where('created_by', $filters['created_by']))
            ->when(filled($filters['q'] ?? null), function (Builder $query) use ($filters): void {
                $needle = (string) $filters['q'];
                $query->where(fn (Builder $inner) => $inner
                    ->where('display_value', 'like', '%'.$needle.'%')
                    ->orWhere('reason', 'like', '%'.$needle.'%')
                    ->orWhere('notes', 'like', '%'.$needle.'%'));
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();
    }

    /** @return array<string, int> */
    public function summary(): array
    {
        return [
            'active' => BlockedListEntry::query()->where('status', 'active')->count(),
            'inactive' => BlockedListEntry::query()->where('status', 'inactive')->count(),
            'expired' => BlockedListEntry::query()->where('status', 'expired')->orWhere(fn ($query) => $query->whereNotNull('expires_at')->where('expires_at', '<=', now()))->count(),
            'manual' => BlockedListEntry::query()->where('source', 'manual')->count(),
        ];
    }

    /** @return array<int, string> */
    private function typesForGroup(string $group): array
    {
        return match ($group) {
            'domains' => ['sender_domain', 'recipient_domain'],
            'recipients' => ['recipient_email_pattern', 'recipient_domain'],
            'ip-rules' => ['ip_address'],
            'comment-rules' => ['comment_email', 'blocked_phrase'],
            default => ['sender_email', 'sender_domain'],
        };
    }
}
