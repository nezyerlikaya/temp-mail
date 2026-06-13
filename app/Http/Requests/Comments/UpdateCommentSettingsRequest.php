<?php

namespace App\Http\Requests\Comments;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommentSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update comment settings') ?? false;
    }

    public function rules(): array
    {
        return [
            'comments_active' => ['nullable', 'boolean'],
            'guest_comments_allowed' => ['nullable', 'boolean'],
            'approval_required' => ['nullable', 'boolean'],
            'verified_email_required' => ['nullable', 'boolean'],
            'auto_close_days' => ['required', 'integer', 'min:0', 'max:3650'],
            'replies_active' => ['nullable', 'boolean'],
            'minimum_length' => ['required', 'integer', 'min:1', 'max:1000'],
            'maximum_length' => ['required', 'integer', 'min:10', 'max:10000', 'gte:minimum_length'],
            'maximum_links' => ['required', 'integer', 'min:0', 'max:20'],
            'blocked_words' => ['nullable', 'string', 'max:3000'],
            'notify_pending_admins' => ['nullable', 'boolean'],
        ];
    }

    /** @return array<string, mixed> */
    public function settings(): array
    {
        $validated = $this->validated();

        return [
            'comments_active' => $this->boolean('comments_active'),
            'guest_comments_allowed' => $this->boolean('guest_comments_allowed'),
            'approval_required' => $this->boolean('approval_required'),
            'verified_email_required' => $this->boolean('verified_email_required'),
            'auto_close_days' => (int) $validated['auto_close_days'],
            'replies_active' => $this->boolean('replies_active'),
            'minimum_length' => (int) $validated['minimum_length'],
            'maximum_length' => (int) $validated['maximum_length'],
            'maximum_links' => (int) $validated['maximum_links'],
            'blocked_words' => collect(preg_split('/\r\n|\r|\n|,/', (string) ($validated['blocked_words'] ?? '')))
                ->map(fn (string $word): string => trim($word))
                ->filter()
                ->unique()
                ->values()
                ->all(),
            'notify_pending_admins' => $this->boolean('notify_pending_admins'),
            'maximum_reply_depth' => 1,
        ];
    }
}
