<?php

namespace App\Services\Translations;

use App\Models\Locale;
use App\Models\TranslationSource;
use App\Models\User;

class TranslationStore
{
    public function __construct(private readonly TranslationSourceRegistry $registry) {}

    public function syncRegistry(): void
    {
        foreach ($this->registry->sources() as $source) {
            TranslationSource::query()->firstOrCreate(
                ['translation_key' => $source['translation_key']],
                $source,
            );
        }
    }

    /** @param array<string, mixed> $data */
    public function create(array $data, User $actor): TranslationSource
    {
        return TranslationSource::query()->create([
            ...$data,
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ])->refresh();
    }

    /** @param array<string, mixed> $data */
    public function update(TranslationSource $source, array $data, User $actor): TranslationSource
    {
        $source->forceFill([
            ...$data,
            'updated_by' => $actor->id,
        ])->save();

        return $source->refresh();
    }

    /** @return array{total: int, active: int, required: int, missing: int} */
    public function summary(): array
    {
        $total = TranslationSource::query()->count();

        return [
            'total' => $total,
            'active' => TranslationSource::query()->where('is_active', true)->count(),
            'required' => TranslationSource::query()->where('is_required', true)->count(),
            'missing' => Locale::query()->where('locale', '!=', 'en')->count() * $total,
        ];
    }
}
