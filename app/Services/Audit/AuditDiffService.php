<?php

namespace App\Services\Audit;

use App\Models\UserAuditEvent;
use Illuminate\Support\Collection;

class AuditDiffService
{
    public function __construct(private readonly AuditSanitizer $sanitizer) {}

    /**
     * @param  Collection<int, UserAuditEvent>  $events
     * @return array<int, array<int, array{field: string, before: mixed, after: mixed}>>
     */
    public function forEvents(Collection $events): array
    {
        return $events
            ->mapWithKeys(fn (UserAuditEvent $event): array => [$event->id => $this->diff($event)])
            ->all();
    }

    /** @return array<int, array{field: string, before: mixed, after: mixed}> */
    public function diff(UserAuditEvent $event): array
    {
        $metadata = $this->sanitizer->sanitize($event->metadata ?? []);

        if (isset($metadata['changes']) && is_array($metadata['changes'])) {
            return collect($metadata['changes'])
                ->filter(fn (mixed $change): bool => is_array($change) && (array_key_exists('old', $change) || array_key_exists('new', $change)))
                ->map(fn (array $change, string $field): array => [
                    'field' => $field,
                    'before' => $this->normalize($change['old'] ?? null),
                    'after' => $this->normalize($change['new'] ?? null),
                ])
                ->values()
                ->all();
        }

        $pairs = collect($metadata)
            ->filter(fn (mixed $value, string $key): bool => str_starts_with($key, 'old_'))
            ->map(function (mixed $before, string $oldKey) use ($metadata): ?array {
                $field = str($oldKey)->after('old_')->toString();
                $newKey = 'new_'.$field;

                if (! array_key_exists($newKey, $metadata)) {
                    return null;
                }

                return [
                    'field' => $field,
                    'before' => $this->normalize($before),
                    'after' => $this->normalize($metadata[$newKey]),
                ];
            })
            ->filter()
            ->values()
            ->all();

        if ($pairs !== []) {
            return $pairs;
        }

        if (isset($metadata['changed_keys']) && is_array($metadata['changed_keys'])) {
            return collect($metadata['changed_keys'])
                ->map(fn (mixed $field): array => [
                    'field' => (string) $field,
                    'before' => 'Changed',
                    'after' => 'Recorded',
                ])
                ->values()
                ->all();
        }

        return [];
    }

    private function normalize(mixed $value): mixed
    {
        if (is_array($value)) {
            return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return $value ?? 'empty';
    }
}
