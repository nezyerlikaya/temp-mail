<?php

namespace App\Http\Requests\Updates;

use Illuminate\Foundation\Http\FormRequest;

class RollbackUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.update-center.rollback') ?? false;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'confirm_readiness' => ['accepted'],
        ];
    }
}
