<?php

namespace App\Actions\Seo;

use App\Models\SeoRecord;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Seo\SeoRecordResolver;
use App\Services\Seo\SeoTargetRegistry;
use Illuminate\Validation\ValidationException;

class CreateSeoRecordAction
{
    public function __construct(
        private readonly SeoTargetRegistry $targets,
        private readonly SeoRecordResolver $resolver,
        private readonly AuditLogger $audit,
    ) {}

    public function handle(User $actor, int $localeId, string $targetType, string $targetKey): SeoRecord
    {
        $target = $this->targets->find($localeId, $targetType, $targetKey);

        if (! $target) {
            throw ValidationException::withMessages([
                'target_key' => 'Choose a valid SEO target for this language.',
            ]);
        }

        $record = SeoRecord::query()->firstOrCreate(
            [
                'locale_id' => $localeId,
                'target_type' => $targetType,
                'target_key' => $targetKey,
            ],
            [
                ...$this->resolver->defaultsForTarget($target),
                'created_by' => $actor->id,
                'updated_by' => $actor->id,
            ],
        );

        $this->audit->record('seo.record_created', $actor, null, [
            'seo_record_id' => $record->id,
            'locale_id' => $record->locale_id,
            'target_type' => $record->target_type,
            'target_key' => $record->target_key,
        ], ['module' => 'seo', 'action' => 'Create SEO record', 'target' => $record]);

        return $record;
    }
}
