<?php

namespace App\Services\Mailboxes;

use App\Models\MailboxRule;
use Illuminate\Support\Carbon;

class MailboxRetentionService
{
    /** @return array<string, string> */
    public function preview(MailboxRule $rules): array
    {
        return [
            'guest_expires' => $this->duration($rules->guest_lifetime_minutes),
            'registered_expires' => $this->duration($rules->registered_lifetime_minutes),
            'premium_expires' => $this->duration($rules->premium_lifetime_minutes).' (readiness)',
            'purge_timing' => $rules->auto_delete_expired
                ? 'Expired mailboxes are purged '.$this->duration($rules->expired_cleanup_delay_hours * 60).' after expiry.'
                : 'Automatic purge is disabled. Expired mailboxes remain available for review.',
            'example_guest_expiry' => Carbon::now()->addMinutes($rules->guest_lifetime_minutes)->toDayDateTimeString(),
        ];
    }

    private function duration(int $minutes): string
    {
        if ($minutes % 1440 === 0) {
            return ($minutes / 1440).' day(s)';
        }
        if ($minutes % 60 === 0) {
            return ($minutes / 60).' hour(s)';
        }

        return $minutes.' minute(s)';
    }
}
