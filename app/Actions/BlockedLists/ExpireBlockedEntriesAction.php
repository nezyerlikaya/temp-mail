<?php

namespace App\Actions\BlockedLists;

use App\Models\User;
use App\Services\BlockedLists\BlockedEntryExpiryService;

class ExpireBlockedEntriesAction
{
    public function __construct(private readonly BlockedEntryExpiryService $expiry) {}

    public function handle(?User $actor = null): int
    {
        return $this->expiry->expire($actor);
    }
}
