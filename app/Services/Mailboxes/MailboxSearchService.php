<?php

namespace App\Services\Mailboxes;

use App\Models\Mailbox;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class MailboxSearchService
{
    /** @param array<string, mixed> $filters */
    public function search(array $filters): LengthAwarePaginator
    {
        return Mailbox::query()
            ->with(['domain', 'user', 'locale'])
            ->when(filled($filters['q'] ?? null), fn (Builder $query) => $query->where('address', 'like', '%'.$this->escape((string) $filters['q']).'%'))
            ->when(($filters['status'] ?? 'all') !== 'all', fn (Builder $query) => $query->where('status', $filters['status']))
            ->when(($filters['domain_id'] ?? 'all') !== 'all', fn (Builder $query) => $query->where('domain_id', $filters['domain_id']))
            ->when(($filters['owner'] ?? 'all') === 'guest', fn (Builder $query) => $query->whereNull('user_id'))
            ->when(($filters['owner'] ?? 'all') === 'registered', fn (Builder $query) => $query->whereNotNull('user_id'))
            ->when(($filters['mailbox_type'] ?? 'all') !== 'all', fn (Builder $query) => $query->where('mailbox_type', $filters['mailbox_type']))
            ->when(($filters['created'] ?? 'all') === 'today', fn (Builder $query) => $query->whereDate('created_at', today()))
            ->when(($filters['created'] ?? 'all') === 'week', fn (Builder $query) => $query->where('created_at', '>=', now()->subDays(7)))
            ->when(($filters['created'] ?? 'all') === 'month', fn (Builder $query) => $query->where('created_at', '>=', now()->subDays(30)))
            ->latest('last_activity_at')
            ->latest('id')
            ->paginate((int) ($filters['per_page'] ?? 15))
            ->withQueryString();
    }

    private function escape(string $value): string
    {
        return str_replace(['%', '_'], ['\\%', '\\_'], trim($value));
    }
}
