<?php

namespace App\Services\Audit;

use App\Models\User;
use App\Models\UserAuditEvent;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditExportService
{
    public function __construct(
        private readonly AuditSearchService $search,
        private readonly AuditSanitizer $sanitizer,
        private readonly AuditLogger $logger,
    ) {}

    /** @param array<string, mixed> $filters */
    public function stream(array $filters, User $actor): StreamedResponse
    {
        $count = $this->search->query($filters)->count();

        $this->logger->record('audit.logs_exported', $actor, $actor, [
            'filters' => $filters,
            'record_count' => $count,
        ], [
            'module' => 'audit',
            'action' => 'Logs exported',
            'severity' => 'critical',
        ]);

        $filename = 'audit-logs-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($filters): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'created_at',
                'event',
                'module',
                'action',
                'severity',
                'actor',
                'subject',
                'ip_address',
                'route_name',
                'request_method',
                'target_type',
                'target_id',
                'target_url',
                'correlation_id',
                'metadata',
            ]);

            $this->search->query($filters)
                ->latest('created_at')
                ->chunk(200, function ($events) use ($handle): void {
                    foreach ($events as $event) {
                        /** @var UserAuditEvent $event */
                        fputcsv($handle, [
                            $event->created_at?->toIso8601String(),
                            $event->event,
                            $event->module,
                            $event->action,
                            $event->severity,
                            $event->actor?->email ?? 'system',
                            $event->subject?->email ?? '',
                            $event->ip_address,
                            $event->route_name,
                            $event->request_method,
                            $event->target_type,
                            $event->target_id,
                            $event->target_url,
                            $event->correlation_id,
                            json_encode($this->sanitizer->sanitize($event->metadata ?? []), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES),
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
