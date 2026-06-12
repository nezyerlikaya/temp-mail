<?php

namespace App\Services\Security;

use App\Models\AbuseSignal;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SecuritySummaryService
{
    /** @return array<string, int> */
    public function metrics(): array
    {
        if (! $this->tableIsReady()) {
            return $this->emptyMetrics();
        }

        $open = AbuseSignal::query()->whereIn('status', ['open', 'reviewing']);

        return [
            'open_alerts' => (clone $open)->count(),
            'critical_alerts' => (clone $open)->where('severity', 'critical')->count(),
            'bot_challenges' => (int) AbuseSignal::query()->where('signal_type', 'bot_challenge')->sum('occurrence_count'),
            'spam_blocked' => (int) AbuseSignal::query()->whereIn('signal_type', ['spam_blocked', 'suspicious_comment'])->sum('occurrence_count'),
            'failed_logins' => (int) AbuseSignal::query()->where('signal_type', 'failed_admin_login')->sum('occurrence_count'),
            'rate_limited_requests' => (int) AbuseSignal::query()->where('signal_type', 'rate_limited_request')->sum('occurrence_count'),
        ];
    }

    /** @return array<string, int> */
    private function emptyMetrics(): array
    {
        return [
            'open_alerts' => 0,
            'critical_alerts' => 0,
            'bot_challenges' => 0,
            'spam_blocked' => 0,
            'failed_logins' => 0,
            'rate_limited_requests' => 0,
        ];
    }

    private function tableIsReady(): bool
    {
        try {
            return Schema::hasTable('abuse_signals');
        } catch (Throwable) {
            return false;
        }
    }
}
