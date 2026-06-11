<?php

namespace App\Http\Requests\Users;

use App\Models\User;
use App\Services\Users\UserProfileService;
use App\Services\Users\UserStatusService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateUserIdentityRequest extends FormRequest
{
    public function authorize(): bool
    {
        $profileUser = $this->route('user');

        return $profileUser instanceof User && $this->user()?->can('update', $profileUser) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var User $profileUser */
        $profileUser = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'min:3', 'max:40', 'alpha_dash:ascii', Rule::unique(User::class)->ignore($profileUser)],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class)->ignore($profileUser)],
            'status' => ['required', Rule::in(array_keys(app(UserStatusService::class)->statuses()))],
            'timezone' => ['required', 'timezone:all'],
            'language_preference' => ['required', Rule::in(array_keys(app(UserProfileService::class)->languages()))],
            'bio' => ['nullable', 'string', 'max:2000'],
            'website' => ['nullable', 'url:http,https', 'max:255'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->user()?->is($this->route('user')) && $this->input('status') === 'suspended') {
                    $validator->errors()->add('status', 'You cannot suspend your own account.');
                }
            },
        ];
    }
}
