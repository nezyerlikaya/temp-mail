<?php

namespace App\Http\Requests\Seo;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class RollbackSeoVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user?->can('admin.seo-growth-center.rollback')) {
            return false;
        }

        return in_array($user->role, [UserRole::Owner->value, UserRole::Admin->value], true);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [];
    }
}
