<?php

namespace App\Http\Requests\Abuse;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignAbuseCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('assign abuse case') ?? false;
    }

    public function rules(): array
    {
        return [
            'assigned_to' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('status', 'active')->whereIn('role', ['owner', 'admin', 'moderator'])),
            ],
        ];
    }
}
