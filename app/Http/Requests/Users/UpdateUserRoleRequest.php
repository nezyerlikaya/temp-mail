<?php

namespace App\Http\Requests\Users;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.roles-permissions.manage') === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'role' => ['required', Rule::enum(UserRole::class)],
            'confirm_critical_change' => [
                Rule::requiredIf(fn (): bool => $this->isCriticalChange()),
                'nullable',
                'accepted',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'confirm_critical_change.required' => 'Confirm that you understand this access change.',
            'confirm_critical_change.accepted' => 'Confirm that you understand this access change.',
        ];
    }

    private function isCriticalChange(): bool
    {
        $subject = $this->route('user');

        if (! $subject instanceof User) {
            return false;
        }

        $current = UserRole::tryFrom((string) $subject->role) ?? UserRole::Member;
        $next = UserRole::tryFrom((string) $this->input('role'));

        return $next !== null && $next !== $current && ($current->isCritical() || $next->isElevated());
    }
}
