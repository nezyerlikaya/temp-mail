<?php

namespace App\Http\Requests\BlockedLists;

use Illuminate\Foundation\Http\FormRequest;

class ToggleBlockedEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('activate deactivate blocked entry') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
