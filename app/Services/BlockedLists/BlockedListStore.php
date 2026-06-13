<?php

namespace App\Services\BlockedLists;

use App\Models\AbuseReport;
use App\Models\BlockedListEntry;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Validation\ValidationException;

class BlockedListStore
{
    public function __construct(
        private readonly BlockedValueNormalizer $normalizer,
        private readonly AuditLogger $audit,
    ) {}

    /** @param array<string, mixed> $payload */
    public function create(User $actor, array $payload): BlockedListEntry
    {
        $normalized = $this->normalizer->normalize((string) $payload['entry_type'], (string) $payload['value']);
        $hash = $this->normalizer->hash($normalized);
        $this->assertNoActiveDuplicate((string) $payload['entry_type'], $hash);

        $entry = BlockedListEntry::query()->create([
            'entry_type' => $payload['entry_type'],
            'normalized_hash' => $hash,
            'encrypted_normalized_value' => $normalized,
            'display_value' => $this->normalizer->display((string) $payload['entry_type'], $normalized),
            'reason' => trim(strip_tags((string) $payload['reason'])),
            'source' => $payload['source'] ?? 'manual',
            'status' => $payload['status'] ?? 'active',
            'starts_at' => $payload['starts_at'] ?? now(),
            'expires_at' => $payload['expires_at'] ?? null,
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
            'related_abuse_report_id' => $this->caseId($payload['related_abuse_case'] ?? null),
            'notes' => filled($payload['notes'] ?? null) ? trim(strip_tags((string) $payload['notes'])) : null,
        ]);

        $this->auditChange('blocked_lists.entry_created', $actor, $entry);

        return $entry->refresh();
    }

    /** @param array<string, mixed> $payload */
    public function update(User $actor, BlockedListEntry $entry, array $payload): BlockedListEntry
    {
        $normalized = $this->normalizer->normalize((string) $payload['entry_type'], (string) $payload['value']);
        $hash = $this->normalizer->hash($normalized);
        $this->assertNoActiveDuplicate((string) $payload['entry_type'], $hash, $entry);

        $entry->forceFill([
            'entry_type' => $payload['entry_type'],
            'normalized_hash' => $hash,
            'encrypted_normalized_value' => $normalized,
            'display_value' => $this->normalizer->display((string) $payload['entry_type'], $normalized),
            'reason' => trim(strip_tags((string) $payload['reason'])),
            'source' => $payload['source'],
            'status' => $payload['status'],
            'starts_at' => $payload['starts_at'] ?? null,
            'expires_at' => $payload['expires_at'] ?? null,
            'updated_by' => $actor->id,
            'related_abuse_report_id' => $this->caseId($payload['related_abuse_case'] ?? null),
            'notes' => filled($payload['notes'] ?? null) ? trim(strip_tags((string) $payload['notes'])) : null,
        ])->save();

        $this->auditChange('blocked_lists.entry_updated', $actor, $entry);

        return $entry->refresh();
    }

    public function activate(User $actor, BlockedListEntry $entry): BlockedListEntry
    {
        $this->assertNoActiveDuplicate($entry->entry_type, $entry->normalized_hash, $entry);
        $entry->forceFill(['status' => 'active', 'updated_by' => $actor->id])->save();
        $this->auditChange('blocked_lists.entry_activated', $actor, $entry);

        return $entry->refresh();
    }

    public function deactivate(User $actor, BlockedListEntry $entry): BlockedListEntry
    {
        $entry->forceFill(['status' => 'inactive', 'updated_by' => $actor->id])->save();
        $this->auditChange('blocked_lists.entry_deactivated', $actor, $entry);

        return $entry->refresh();
    }

    private function assertNoActiveDuplicate(string $type, string $hash, ?BlockedListEntry $except = null): void
    {
        $duplicate = BlockedListEntry::query()
            ->where('entry_type', $type)
            ->where('normalized_hash', $hash)
            ->where('status', 'active')
            ->when($except, fn ($query) => $query->whereKeyNot($except->getKey()))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages(['value' => 'An active blocked-list entry already exists for this normalized value.']);
        }
    }

    private function caseId(?string $reference): ?int
    {
        if (! filled($reference)) {
            return null;
        }

        return AbuseReport::query()->where('case_reference', $reference)->value('id');
    }

    private function auditChange(string $event, User $actor, BlockedListEntry $entry): void
    {
        $this->audit->record($event, $actor, null, [
            'entry_id' => $entry->id,
            'entry_type' => $entry->entry_type,
            'display_value' => $entry->display_value,
            'normalized_hash' => $entry->normalized_hash,
            'source' => $entry->source,
            'status' => $entry->status,
            'related_abuse_case' => $entry->abuseReport?->case_reference,
        ], ['module' => 'mail-infrastructure', 'target' => $entry]);
    }
}
