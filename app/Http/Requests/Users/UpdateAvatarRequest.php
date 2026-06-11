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
            'avatar_media_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'avatar_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'remove_avatar' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $removeAvatar = $this->boolean('remove_avatar');

        $this->merge([
            'remove_avatar' => $removeAvatar,
            'avatar_media_id' => $removeAvatar ? null : $this->input('avatar_media_id'),
        ]);
    }
}
