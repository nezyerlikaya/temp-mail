<?php

namespace App\Services\Analytics;

use App\Models\AnalyticsDailyMetric;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsExportService
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** @param array{from: Carbon, to: Carbon} $range @param array<string, mixed> $filters */
    public function csv(User $actor, array $range, array $filters): StreamedResponse
    {
        $filename = 'product-analytics-'.$range['from']->toDateString().'-'.$range['to']->toDateString().'.csv';

        $this->audit->record('analytics.exported', $actor, null, [
            'date_from' => $range['from']->toDateString(),
            'date_to' => $range['to']->toDateString(),
            'locale_id' => $filters['locale_id'] ?? 'all',
            'domain_id' => $filters['domain_id'] ?? 'all',
            'format' => 'csv',
        ], ['module' => 'analytics', 'action' => 'CSV export']);

        return response()->streamDownload(function () use ($range, $filters): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['metric_date', 'event_key', 'locale_id', 'domain_id', 'total_count', 'unique_visitors']);

            AnalyticsDailyMetric::query()
                ->whereDate('metric_date', '>=', $range['from']->toDateString())
                ->whereDate('metric_date', '<=', $range['to']->toDateString())
                ->when(($filters['locale_id'] ?? 'all') !== 'all', fn ($query) => $query->where('locale_id', $filters['locale_id']))
                ->when(($filters['domain_id'] ?? 'all') !== 'all', fn ($query) => $query->where('domain_id', $filters['domain_id']))
                ->orderBy('metric_date')
                ->orderBy('event_key')
                ->lazy()
                ->each(function (AnalyticsDailyMetric $metric) use ($handle): void {
                    fputcsv($handle, [
                        $metric->metric_date->toDateString(),
                        $metric->event_key,
                        $metric->locale_id,
                        $metric->domain_id,
                        $metric->total_count,
                        $metric->unique_visitors,
                    ]);
                });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
