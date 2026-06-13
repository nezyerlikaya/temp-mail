<?php

namespace App\Actions\BlockedLists;

use App\Models\User;
use App\Services\BlockedLists\BlockedListImportService;

class ImportBlockedEntriesAction
{
    public function __construct(private readonly BlockedListImportService $imports) {}

    /** @return array<string, mixed> */
    public function preview(string $csv): array
    {
        return $this->imports->preview($csv);
    }

    /** @return array<string, mixed> */
    public function handle(User $actor, string $csv): array
    {
        return $this->imports->import($actor, $csv);
    }
}
