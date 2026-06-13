<?php

namespace App\Actions\Translations;

use App\Models\Locale;
use App\Models\TranslationSource;
use App\Models\TranslationValue;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Translations\TranslationValueSanitizer;
use Illuminate\Support\Facades\DB;

class SaveTranslationsAction
{
    public function __construct(
        private readonly TranslationValueSanitizer $sanitizer,
        private readonly AuditLogger $audit,
    ) {}

    /** @param array<int|string, array{value?: string|null, status?: string|null}> $translations */
    public function handle(User $actor, Locale $locale, array $translations): int
    {
        $saved = DB::transaction(function () use ($actor, $locale, $translations): int {
            $sources = TranslationSource::query()
                ->where('is_active', true)
                ->whereIn('id', array_map('intval', array_keys($translations)))
                ->get()
                ->keyBy('id');
            $saved = 0;

            foreach ($translations as $sourceId => $payload) {
                $source = $sources->get((int) $sourceId);
                if (! $source instanceof TranslationSource) {
                    continue;
                }

                $value = $this->sanitizer->sanitize($source, $payload['value'] ?? null);
                $existing = TranslationValue::query()
                    ->where('translation_source_id', $source->id)
                    ->where('locale_id', $locale->id)
                    ->first();

                if ($existing === null && $value === null) {
                    continue;
                }

                $changed = $existing === null || $existing->value !== $value;
                $requestedStatus = in_array($payload['status'] ?? null, ['draft', 'translated'], true)
                    ? $payload['status']
                    : 'draft';
                $status = $value === null ? 'missing' : ($changed ? $requestedStatus : $existing->status);

                TranslationValue::query()->updateOrCreate(
                    ['translation_source_id' => $source->id, 'locale_id' => $locale->id],
                    [
                        'value' => $value,
                        'status' => $status,
                        'updated_by' => $actor->id,
                        'reviewed_by' => $changed ? null : $existing?->reviewed_by,
                        'reviewed_at' => $changed ? null : $existing?->reviewed_at,
                        'published_by' => $changed ? null : $existing?->published_by,
                        'published_at' => $changed ? null : $existing?->published_at,
                    ],
                );
                $saved++;
            }

            return $saved;
        });

        $this->audit->record('translation.values_updated', $actor, null, [
            'locale' => $locale->locale,
            'source_count' => $saved,
        ], ['module' => 'localization']);

        return $saved;
    }
}
