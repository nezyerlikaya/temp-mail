<?php

namespace App\Services\Dashboard;

use App\Models\AbuseSignal;
use App\Models\Domain;
use App\Models\InboundMailConnection;
use App\Models\SmtpConnection;
use App\Models\SystemBackup;
use App\Models\SystemHealthCheck;
use App\Models\UpdateCheck;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Throwable;

class DashboardCriticalAlertService
{
    /** @return array<int, array<string, mixed>> */
    public function alerts(User $user): array
    {
        return collect([
            $this->domainOffline($user),
            $this->mailConnectionFailed($user),
            $this->updateFailed($user),
            $this->backupFailed($user),
            $this->criticalSystemHealth($user),
            $this->criticalSecurityAlert($user),
        ])->filter()->unique('key')->values()->all();
    }

    /** @return array<string, mixed>|null */
    private function domainOffline(User $user): ?array
    {
        $count = $this->count('domains', fn (): int => Domain::query()->where('status', 'offline')->count());

        return $count > 0 ? $this->alert($user, 'domain_offline', 'Domain offline', $count.' domain records are offline.', 'critical', 'admin.domains.index', 'admin.domains.view') : null;
    }

    /** @return array<string, mixed>|null */
    private function mailConnectionFailed(User $user): ?array
    {
        $failed = $this->count('inbound_mail_connections', fn (): int => InboundMailConnection::query()->where('status', 'failed')->count())
            + $this->count('smtp_connections', fn (): int => SmtpConnection::query()->where('status', 'failed')->count());

        return $failed > 0 ? $this->alert($user, 'mail_connection_failed', 'Mail connection failed', $failed.' IMAP or SMTP connection tests failed.', 'critical', 'admin.imap-smtp.index', 'admin.imap-smtp.view') : null;
    }

    /** @return array<string, mixed>|null */
    private function updateFailed(User $user): ?array
    {
        $failed = $this->value('update_checks', fn (): bool => UpdateCheck::query()->latest('checked_at')->value('status') === 'failed', false);

        return $failed ? $this->alert($user, 'update_failed', 'Update check failed', 'The latest update check ended in a clean failure state.', 'warning', 'admin.update-center.index', 'admin.update-center.view') : null;
    }

    /** @return array<string, mixed>|null */
    private function backupFailed(User $user): ?array
    {
        $failed = $this->value('system_backups', fn (): bool => SystemBackup::query()->latest('created_at')->value('status') === 'failed', false);

        return $failed ? $this->alert($user, 'backup_failed', 'Backup failed', 'The latest backup job failed and needs review.', 'critical', 'admin.backups-health.index', 'admin.backups-health.view') : null;
    }

    /** @return array<string, mixed>|null */
    private function criticalSystemHealth(User $user): ?array
    {
        $critical = $this->value('system_health_checks', fn (): bool => SystemHealthCheck::query()->latest('checked_at')->value('overall_status') === 'critical', false);

        return $critical ? $this->alert($user, 'critical_system_health', 'Critical system health', 'The latest system health run reported critical issues.', 'critical', 'admin.backups-health.index', 'admin.backups-health.view') : null;
    }

    /** @return array<string, mixed>|null */
    private function criticalSecurityAlert(User $user): ?array
    {
        $count = $this->count('abuse_signals', fn (): int => AbuseSignal::query()->where('status', 'open')->where('severity', 'critical')->sum('occurrence_count'));

        return $count > 0 ? $this->alert($user, 'critical_security_alert', 'Critical security alert', $count.' critical security signal occurrences are open.', 'critical', 'admin.security-defense-center.index', 'admin.security-defense-center.view') : null;
    }

    /** @return array<string, mixed> */
    private function alert(User $user, string $key, string $title, string $message, string $severity, string $route, string $ability): array
    {
        $canViewTarget = Gate::forUser($user)->allows($ability);

        return [
            'key' => $key,
            'title' => $title,
            'message' => $message,
            'severity' => $severity,
            'route' => $canViewTarget ? $route : null,
            'url' => $canViewTarget ? route($route) : null,
        ];
    }

    private function count(string $table, callable $callback): int
    {
        return (int) $this->value($table, $callback, 0);
    }

    private function value(string $table, callable $callback, mixed $fallback): mixed
    {
        try {
            if (! Schema::hasTable($table)) {
                return $fallback;
            }

            return $callback();
        } catch (Throwable) {
            return $fallback;
        }
    }
}
