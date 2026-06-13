<?php

namespace App\Http\Requests\Comments;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CommentFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('view comments') ?? false;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:120'],
            'post_id' => ['nullable', 'integer', Rule::exists('blog_posts', 'id')],
            'locale_id' => ['nullable', 'integer', Rule::exists('locales', 'id')],
            'status' => ['nullable', Rule::in(['pending', 'approved', 'spam', 'trashed', 'all'])],
            'spam_score' => ['nullable', Rule::in(['all', 'high', 'low'])],
            'date' => ['nullable', 'date'],
            'has_links' => ['nullable', Rule::in(['all', 'yes', 'no'])],
            'akismet' => ['nullable', 'string', 'max:40'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:30'],
        ];
    }

    public function filters(): array
    {
        return [
            'q' => (string) $this->validated('q', ''),
            'post_id' => $this->validated('post_id'),
            'locale_id' => $this->validated('locale_id'),
            'status' => (string) $this->validated('status', 'pending'),
            'spam_score' => (string) $this->validated('spam_score', 'all'),
            'date' => $this->validated('date'),
            'has_links' => (string) $this->validated('has_links', 'all'),
            'akismet' => (string) $this->validated('akismet', 'all'),
            'per_page' => (int) $this->validated('per_page', 10),
        ];
    }
}
