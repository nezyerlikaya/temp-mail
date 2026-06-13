<?php

namespace App\Actions\BlockedLists;

use App\Models\BlockedListEntry;
use App\Models\User;
use App\Services\BlockedLists\BlockedListStore;

class CreateBlockedEntryAction
{
    public function __construct(private readonly BlockedListStore $store) {}

    /** @param array<string, mixed> $payload */
    public function handle(User $actor, array $payload): BlockedListEntry
    {
        return $this->store->create($actor, $payload);
    }
}
