<?php

namespace App\Services\Blocklists;

use App\Models\AbuseBlocklistEntry;
use App\Models\AbuseReport;
use App\Models\User;
use Illuminate\Support\Str;

class BlocklistService
{
    public function add(User $actor, AbuseReport $report, string $type, string $value): AbuseBlocklistEntry
    {
        $normalized = $this->normalize($type, $value);

        return AbuseBlocklistEntry::query()->updateOrCreate(
            ['type' => $type, 'value_hash' => hash('sha256', $normalized)],
            [
                'abuse_report_id' => $report->id,
                'encrypted_value' => $normalized,
                'value_preview' => $this->preview($type, $normalized),
                'status' => 'active',
                'created_by' => $actor->id,
            ],
        );
    }

    private function normalize(string $type, string $value): string
    {
        return in_array($type, ['sender_email', 'sender_domain', 'recipient_email', 'recipient_domain'], true)
            ? Str::lower(trim($value))
            : trim($value);
    }

    private function preview(string $type, string $value): string
    {
        if ($type === 'blocked_ip_hash') {
            return 'Hash '.substr(hash('sha256', $value), 0, 12).'...';
        }

        if (in_array($type, ['sender_email', 'recipient_email'], true)) {
            [$name, $domain] = array_pad(explode('@', $value, 2), 2, '');

            return substr($name, 0, 2).'***@'.$domain;
        }

        return Str::limit($value, 80);
    }
}
