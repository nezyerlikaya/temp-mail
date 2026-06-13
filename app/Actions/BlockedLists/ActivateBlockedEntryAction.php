<?php

namespace App\Actions\BlockedLists;

use App\Models\BlockedListEntry;
use App\Models\User;
use App\Services\BlockedLists\BlockedListStore;

class ActivateBlockedEntryAction
{
    public function __construct(private readonly BlockedListStore $store) {}

    public function handle(User $actor, BlockedListEntry $entry): BlockedListEntry
    {
        return $this->store->activate($actor, $entry);
    }
}
