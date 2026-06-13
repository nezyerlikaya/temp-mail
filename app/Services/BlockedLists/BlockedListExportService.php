<?php

namespace App\Services\BlockedLists;

use App\Models\BlockedListEntry;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BlockedListExportService
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** @param array<string, mixed> $filters */
    public function export(User $actor, array $filters, bool $includeSensitiveIp): StreamedResponse
    {
        $filename = 'blocked-list-export-'.now()->format('Ymd-His').'.csv';

        $this->audit->record('blocked_lists.exported', $actor, null, [
            'filters' => array_filter($filters, fn ($value): bool => filled($value) && $value !== 'all'),
            'sensitive_ip_included' => $includeSensitiveIp,
        ], ['module' => 'mail-infrastructure']);

        return response()->streamDownload(function () use ($filters, $includeSensitiveIp): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['entry_type', 'display_value', 'normalized_value', 'reason', 'source', 'status', 'starts_at', 'expires_at']);

            BlockedListEntry::query()
                ->when(($filters['entry_type'] ?? 'all') !== 'all', fn ($query) => $query->where('entry_type', $filters['entry_type']))
                ->when(($filters['status'] ?? 'all') !== 'all', fn ($query) => $query->where('status', $filters['status']))
                ->when(($filters['source'] ?? 'all') !== 'all', fn ($query) => $query->where('source', $filters['source']))
                ->orderBy('entry_type')
                ->orderBy('display_value')
                ->chunk(100, function ($entries) use ($handle, $includeSensitiveIp): void {
                    foreach ($entries as $entry) {
                        fputcsv($handle, [
                            $entry->entry_type,
                            $entry->display_value,
                            $entry->entry_type === 'ip_address' && ! $includeSensitiveIp ? $entry->display_value : $entry->encrypted_normalized_value,
                            $entry->reason,
                            $entry->source,
                            $entry->status,
                            $entry->starts_at?->toDateString(),
                            $entry->expires_at?->toDateString(),
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
