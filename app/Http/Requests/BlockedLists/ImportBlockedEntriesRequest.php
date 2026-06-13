<?php

namespace App\Http\Requests\BlockedLists;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ImportBlockedEntriesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('import blocked entries') ?? false;
    }

    public function rules(): array
    {
        return [
            'mode' => ['required', Rule::in(['preview', 'import'])],
            'csv' => ['required', 'string', 'max:60000'],
        ];
    }
}
