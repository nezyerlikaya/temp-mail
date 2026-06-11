<?php

namespace App\Http\Requests\Users;

use App\Models\User;
use App\Services\Users\AuthorProfileService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateAuthorProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        $profileUser = $this->route('user');

        return $profileUser instanceof User && $this->user()?->can('updateAuthorProfile', $profileUser) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var User $profileUser */
        $profileUser = $this->route('user');

        return [
            'display_name' => ['required', 'string', 'max:255'],
            'public_author_slug' => ['nullable', 'string', 'min:3', 'max:80', 'alpha_dash:ascii', Rule::unique(User::class)->ignore($profileUser)],
            'author_bio' => ['nullable', 'string', 'max:3000'],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'social_links' => ['nullable', 'array:'.implode(',', array_keys(app(AuthorProfileService::class)->socialPlatforms()))],
            'social_links.*' => ['nullable', 'url:http,https', 'max:255'],
            'author_profile_active' => ['nullable', 'boolean'],
            'featured_author' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'author_profile_active' => $this->boolean('author_profile_active'),
            'featured_author' => $this->boolean('featured_author'),
        ]);
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->boolean('author_profile_active') && blank($this->input('public_author_slug'))) {
                    $validator->errors()->add('public_author_slug', 'A public author slug is required before publishing.');
                }

                if ($this->boolean('author_profile_active') && $this->route('user')?->status !== 'active') {
                    $validator->errors()->add('author_profile_active', 'Suspended or inactive accounts cannot publish an author profile.');
                }
            },
        ];
    }
}
