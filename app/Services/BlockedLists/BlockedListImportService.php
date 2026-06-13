<?php

namespace App\Services\BlockedLists;

use App\Models\BlockedListEntry;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Notifications\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Throwable;

class BlockedListImportService
{
    private const MAX_ROWS = 100;

    public function __construct(
        private readonly BlockedValueNormalizer $normalizer,
        private readonly BlockedListCacheService $cache,
        private readonly AuditLogger $audit,
        private readonly NotificationService $notifications,
    ) {}

    /** @return array{valid: bool, rows: array<int, array<string, mixed>>, errors: array<int, string>, counts: array<string, int>} */
    public function preview(string $csv): array
    {
        $rows = $this->parse($csv);
        $preview = [];
        $errors = [];
        $seen = [];

        foreach ($rows as $index => $row) {
            try {
                Validator::make($row, [
                    'entry_type' => ['required', 'in:sender_email,sender_domain,recipient_email_pattern,recipient_domain,ip_address,comment_email,blocked_phrase'],
                    'value' => ['required', 'string', 'max:255'],
                    'reason' => ['required', 'string', 'min:8', 'max:2000'],
                    'source' => ['required', 'in:manual,abuse_report,security_review,comment_moderation'],
                    'status' => ['required', 'in:active,inactive,expired'],
                    'expires_at' => ['nullable', 'date'],
                ])->validate();

                $normalized = $this->normalizer->normalize($row['entry_type'], $row['value']);
                $hash = $this->normalizer->hash($normalized);
                $duplicate = BlockedListEntry::query()
                    ->where('entry_type', $row['entry_type'])
                    ->where('normalized_hash', $hash)
                    ->where('status', 'active')
                    ->exists();
                $batchKey = $row['entry_type'].'|'.$hash;

                if ($duplicate || isset($seen[$batchKey])) {
                    $errors[] = 'Row '.($index + 2).' duplicates an active rule.';
                }

                $seen[$batchKey] = true;
                $preview[] = [
                    ...$row,
                    'line' => $index + 2,
                    'normalized_hash' => $hash,
                    'display_value' => $this->normalizer->display($row['entry_type'], $normalized),
                    'valid' => ! $duplicate,
                ];
            } catch (ValidationException $exception) {
                $errors[] = 'Row '.($index + 2).': '.collect($exception->errors())->flatten()->first();
            }
        }

        return [
            'valid' => $errors === [] && $preview !== [],
            'rows' => $preview,
            'errors' => $errors,
            'counts' => ['rows' => count($preview), 'errors' => count($errors), 'limit' => self::MAX_ROWS],
        ];
    }

    /** @return array{created: int, preview: array<string, mixed>} */
    public function import(User $actor, string $csv): array
    {
        $preview = $this->preview($csv);

        if (! $preview['valid']) {
            $this->notifyFailedImport($preview);
            throw ValidationException::withMessages(['csv' => 'Resolve CSV preview errors before importing.']);
        }

        try {
            $created = DB::transaction(function () use ($actor, $preview): int {
                $count = 0;

                foreach ($preview['rows'] as $row) {
                    $normalized = $this->normalizer->normalize($row['entry_type'], $row['value']);
                    BlockedListEntry::query()->create([
                        'entry_type' => $row['entry_type'],
                        'normalized_hash' => $this->normalizer->hash($normalized),
                        'encrypted_normalized_value' => $normalized,
                        'display_value' => $this->normalizer->display($row['entry_type'], $normalized),
                        'reason' => $row['reason'],
                        'source' => $row['source'],
                        'status' => $row['status'],
                        'starts_at' => now(),
                        'expires_at' => $row['expires_at'] ?: null,
                        'created_by' => $actor->id,
                        'updated_by' => $actor->id,
                        'notes' => null,
                    ]);
                    $count++;
                }

                return $count;
            });
        } catch (Throwable) {
            $this->notifyFailedImport($preview);
            throw ValidationException::withMessages(['csv' => 'Import failed safely. No partial rows were saved.']);
        }

        $this->cache->invalidate();
        $this->audit->record('blocked_lists.imported', $actor, null, [
            'created' => $created,
            'row_count' => $preview['counts']['rows'],
        ], ['module' => 'mail-infrastructure']);

        if ($created >= 25) {
            $this->notifications->dispatch([
                'event_key' => 'blocked_list_large_change',
                'type' => 'mail-infrastructure',
                'severity' => 'warning',
                'title' => 'Large blocked-list import completed',
                'message' => $created.' blocked-list entries were imported after preview.',
                'related_module' => 'mail-infrastructure',
                'action_route' => 'admin.blocked-lists.index',
            ], sendEmail: false);
        }

        return ['created' => $created, 'preview' => $preview];
    }

    /** @return array<int, array{entry_type: string, value: string, reason: string, source: string, status: string, expires_at: string|null}> */
    private function parse(string $csv): array
    {
        $csv = trim($csv);
        if ($csv === '') {
            throw ValidationException::withMessages(['csv' => 'Paste CSV rows before previewing import.']);
        }

        $lines = preg_split('/\r\n|\n|\r/', $csv) ?: [];
        if (count($lines) > self::MAX_ROWS + 1) {
            throw ValidationException::withMessages(['csv' => 'CSV imports are limited to '.self::MAX_ROWS.' rows per transaction.']);
        }

        $header = array_map(fn (string $value): string => strtolower(trim($value)), str_getcsv(array_shift($lines) ?: ''));
        $required = ['entry_type', 'value', 'reason', 'source', 'status', 'expires_at'];

        if ($header !== $required) {
            throw ValidationException::withMessages(['csv' => 'CSV header must be: '.implode(',', $required)]);
        }

        return collect($lines)
            ->filter(fn (string $line): bool => trim($line) !== '')
            ->map(function (string $line) use ($header): array {
                $values = str_getcsv($line);
                $row = array_combine($header, array_pad($values, count($header), ''));

                return [
                    'entry_type' => trim((string) $row['entry_type']),
                    'value' => trim((string) $row['value']),
                    'reason' => trim(strip_tags((string) $row['reason'])),
                    'source' => trim((string) $row['source']) ?: 'manual',
                    'status' => trim((string) $row['status']) ?: 'active',
                    'expires_at' => trim((string) $row['expires_at']) ?: null,
                ];
            })
            ->values()
            ->all();
    }

    /** @param array<string, mixed> $preview */
    private function notifyFailedImport(array $preview): void
    {
        $this->notifications->dispatch([
            'event_key' => 'blocked_list_import_failed',
            'type' => 'mail-infrastructure',
            'severity' => 'warning',
            'title' => 'Blocked-list import failed',
            'message' => 'A blocked-list CSV import was rejected during validation.',
            'related_module' => 'mail-infrastructure',
            'action_route' => 'admin.blocked-lists.index',
            'action_parameters' => ['group' => 'all'],
        ], sendEmail: false);
    }
}
