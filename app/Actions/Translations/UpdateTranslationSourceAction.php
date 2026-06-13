<?php

namespace App\Actions\Translations;

use App\Models\TranslationSource;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Translations\TranslationStore;

class UpdateTranslationSourceAction
{
    public function __construct(
        private readonly TranslationStore $store,
        private readonly AuditLogger $audit,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(User $actor, TranslationSource $source, array $data): TranslationSource
    {
        $source = $this->store->update($source, $data, $actor);

        $this->audit->record('translation_source.updated', $actor, null, [
            'translation_key' => $source->translation_key,
            'group_key' => $source->group_key,
        ], ['module' => 'localization', 'target' => $source]);

        return $source;
    }
}
