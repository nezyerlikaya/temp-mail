<?php

namespace App\Services\Dashboard;

use App\Models\Domain;
use App\Models\InboundMailConnection;
use App\Models\SmtpConnection;
use App\Models\SystemBackup;
use App\Models\SystemHealthCheck;
use Illuminate\Support\Facades\Schema;
use Throwable;

class DashboardHealthSummaryService
{
    /** @return array<int, array<string, mixed>> */
    public function items(): array
    {
        return [
            $this->item('Domain Health', $this->domainStatus(), 'DNS, public availability, and catch-all readiness.', 'admin.domains.index'),
            $this->item('IMAP Readiness', $this->connectionStatus('inbound_mail_connections', InboundMailConnection::class), 'Inbound mail connection tests.', 'admin.imap-smtp.index'),
            $this->item('SMTP Readiness', $this->connectionStatus('smtp_connections', SmtpConnection::class), 'Outbound system mail readiness.', 'admin.imap-smtp.index'),
            $this->item('System Health', $this->latestStatus('system_health_checks', SystemHealthCheck::class, 'overall_status', 'checked_at'), 'Runtime, storage, database, and security checks.', 'admin.backups-health.index'),
            $this->item('Backup Status', $this->latestStatus('system_backups', SystemBackup::class, 'status', 'created_at'), 'Latest backup job outcome.', 'admin.backups-health.index'),
        ];
    }

    /** @return array<string, string> */
    private function item(string $label, string $status, string $detail, string $route): array
    {
        return [
            'label' => $label,
            'status' => $status,
            'detail' => $detail,
            'route' => $route,
            'tone' => in_array($status, ['healthy', 'connected', 'completed', 'ready'], true) ? 'healthy' : 'attention',
        ];
    }

    private function domainStatus(): string
    {
        try {
            if (! Schema::hasTable('domains')) {
                return 'unavailable';
            }

            if (Domain::query()->count() === 0) {
                return 'not_configured';
            }

            return Domain::query()->whereIn('status', ['degraded', 'offline', 'pending_dns', 'draft'])->exists() ? 'attention' : 'ready';
        } catch (Throwable) {
            return 'unavailable';
        }
    }

    private function connectionStatus(string $table, string $model): string
    {
        try {
            if (! Schema::hasTable($table)) {
                return 'unavailable';
            }

            if ($model::query()->count() === 0) {
                return 'not_configured';
            }

            return $model::query()->where('is_active', true)->where('status', 'connected')->exists() ? 'connected' : 'attention';
        } catch (Throwable) {
            return 'unavailable';
        }
    }

    private function latestStatus(string $table, string $model, string $column, string $latestColumn): string
    {
        try {
            if (! Schema::hasTable($table)) {
                return 'unavailable';
            }

            return (string) ($model::query()->latest($latestColumn)->value($column) ?? 'not_run');
        } catch (Throwable) {
            return 'unavailable';
        }
    }
}
