<?php

namespace App\Http\Requests\Users;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        $profileUser = $this->route('user');

        return $profileUser instanceof User && $this->user()?->can('updateAvatar', $profileUser) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'avatar_media_id' => ['nullable', 'integer', 'min:1'],
            'avatar_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'remove_avatar' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['remove_avatar' => $this->boolean('remove_avatar')]);
    }
}
