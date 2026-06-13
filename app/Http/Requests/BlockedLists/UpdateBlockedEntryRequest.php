<?php

namespace App\Http\Requests\BlockedLists;

class UpdateBlockedEntryRequest extends StoreBlockedEntryRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update blocked entry') ?? false;
    }
}
