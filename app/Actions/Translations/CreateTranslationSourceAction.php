<?php

namespace App\Actions\Translations;

use App\Models\TranslationSource;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Translations\TranslationStore;

class CreateTranslationSourceAction
{
    public function __construct(
        private readonly TranslationStore $store,
        private readonly AuditLogger $audit,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(User $actor, array $data): TranslationSource
    {
        $source = $this->store->create($data, $actor);

        $this->audit->record('translation_source.created', $actor, null, [
            'translation_key' => $source->translation_key,
            'group_key' => $source->group_key,
        ], ['module' => 'localization', 'target' => $source]);

        return $source;
    }
}
