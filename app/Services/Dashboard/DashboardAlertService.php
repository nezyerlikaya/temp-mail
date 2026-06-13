<?php

namespace App\Services\Dashboard;

use App\Models\AbuseSignal;
use App\Models\Locale;
use App\Models\SystemHealthCheck;
use App\Models\SystemNotification;
use App\Models\UpdateCheck;
use Illuminate\Support\Facades\Schema;
use Throwable;

class DashboardAlertService
{
    /** @return array<int, array<string, mixed>> */
    public function alerts(bool $includeSensitive = true): array
    {
        $alerts = [];

        if ($includeSensitive && $this->openAbuseAlerts() > 0) {
            $alerts[] = $this->alert('Abuse alerts need review', $this->openAbuseAlerts().' open security or spam signals are waiting.', 'critical', 'admin.security-defense-center.index');
        }

        if ($this->pendingComments() > 0) {
            $alerts[] = $this->alert('Pending comments', $this->pendingComments().' comment moderation notifications are open.', 'warning', 'admin.comment-moderation.index');
        }

        if ($this->healthIsCritical()) {
            $alerts[] = $this->alert('System health attention', 'Latest health check is not healthy.', 'warning', 'admin.backups-health.index');
        }

        if ($this->updateAvailable()) {
            $alerts[] = $this->alert('Update available', 'A newer signed update manifest has been detected.', 'info', 'admin.update-center.index');
        }

        if ($this->localesNeedAttention() > 0) {
            $alerts[] = $this->alert('Locale readiness', $this->localesNeedAttention().' locales require launch attention.', 'warning', 'admin.locale-launch-center.index');
        }

        return $alerts;
    }

    /** @return array<string, mixed> */
    private function alert(string $title, string $message, string $severity, string $route): array
    {
        return compact('title', 'message', 'severity', 'route');
    }

    private function openAbuseAlerts(): int
    {
        return $this->count('abuse_signals', fn (): int => AbuseSignal::query()->where('status', 'open')->sum('occurrence_count'));
    }

    private function pendingComments(): int
    {
        return $this->count('system_notifications', fn (): int => SystemNotification::query()->where('event_key', 'new_pending_comment')->whereNull('archived_at')->count());
    }

    private function healthIsCritical(): bool
    {
        return $this->value('system_health_checks', fn (): bool => in_array(SystemHealthCheck::query()->latest('checked_at')->value('overall_status'), ['critical', 'warning'], true), false);
    }

    private function updateAvailable(): bool
    {
        return $this->value('update_checks', fn (): bool => UpdateCheck::query()->latest('checked_at')->value('status') === 'update_available', false);
    }

    private function localesNeedAttention(): int
    {
        return $this->count('locales', fn (): int => Locale::query()->where('market_readiness', '!=', 'ready')->count());
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
