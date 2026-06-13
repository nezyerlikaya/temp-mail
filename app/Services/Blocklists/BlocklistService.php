<?php

namespace App\Services\Blocklists;

use App\Models\AbuseBlocklistEntry;
use App\Models\AbuseReport;
use App\Models\BlockedListEntry;
use App\Models\User;
use App\Services\BlockedLists\BlockedValueNormalizer;
use Illuminate\Support\Str;

class BlocklistService
{
    public function __construct(private readonly BlockedValueNormalizer $normalizer) {}

    public function add(User $actor, AbuseReport $report, string $type, string $value): AbuseBlocklistEntry
    {
        $entryType = $this->entryType($type);
        $normalized = $this->normalizer->normalize($entryType, $value);
        $preview = $this->normalizer->display($entryType, $normalized);

        BlockedListEntry::query()->updateOrCreate(
            ['entry_type' => $entryType, 'normalized_hash' => $this->normalizer->hash($normalized), 'status' => 'active'],
            [
                'encrypted_normalized_value' => $normalized,
                'display_value' => $preview,
                'reason' => 'Approved from abuse case '.$report->case_reference.'.',
                'source' => 'abuse_report',
                'starts_at' => now(),
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
                'related_abuse_report_id' => $report->id,
            ],
        );

        return AbuseBlocklistEntry::query()->updateOrCreate(
            ['type' => $type, 'value_hash' => hash('sha256', $normalized)],
            [
                'abuse_report_id' => $report->id,
                'encrypted_value' => $normalized,
                'value_preview' => $preview,
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

    private function entryType(string $type): string
    {
        return match ($type) {
            'sender_email' => 'sender_email',
            'sender_domain' => 'sender_domain',
            'recipient_email' => 'recipient_email_pattern',
            'recipient_domain' => 'recipient_domain',
            default => 'ip_address',
        };
    }
}
