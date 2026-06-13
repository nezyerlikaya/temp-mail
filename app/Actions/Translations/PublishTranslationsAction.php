<?php

namespace App\Actions\Translations;

use App\Models\Locale;
use App\Models\TranslationValue;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PublishTranslationsAction
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** @param array<int, int|string> $sourceIds */
    public function handle(User $actor, Locale $locale, array $sourceIds): int
    {
        $values = TranslationValue::query()
            ->where('locale_id', $locale->id)
            ->whereIn('translation_source_id', $sourceIds)
            ->where('status', 'reviewed')
            ->whereNotNull('value')
            ->where('value', '!=', '')
            ->get();

        if ($values->count() !== count(array_unique(array_map('intval', $sourceIds)))) {
            throw ValidationException::withMessages(['source_ids' => 'Translations must be reviewed before publishing.']);
        }

        DB::transaction(function () use ($values, $actor): void {
            foreach ($values as $value) {
                $value->forceFill([
                    'status' => 'published',
                    'published_by' => $actor->id,
                    'published_at' => now(),
                ])->save();
            }
        });

        $this->audit->record('translation.values_published', $actor, null, [
            'locale' => $locale->locale,
            'source_count' => $values->count(),
        ], ['module' => 'localization']);

        return $values->count();
    }
}
