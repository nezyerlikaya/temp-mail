<?php

namespace App\Http\Requests\Pages;

use App\Services\Pages\PageSlugService;
use App\Services\Pages\PageStore;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePageRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (blank($this->input('slug')) && filled($this->input('title'))) {
            $this->merge([
                'slug' => app(PageSlugService::class)->fromTitle((string) $this->input('title')),
            ]);
        }
    }

    public function authorize(): bool
    {
        if (! ($this->user()?->can('admin.page-studio.create') ?? false)) {
            return false;
        }

        return match ($this->input('intent')) {
            'publish' => $this->user()?->can('admin.page-studio.publish') ?? false,
            'hide' => $this->user()?->can('admin.page-studio.hide') ?? false,
            default => true,
        };
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $store = app(PageStore::class);

        return [
            'locale_id' => ['required', 'integer', 'exists:locales,id'],
            'title' => ['required', 'string', 'max:160'],
            'slug' => [
                'nullable',
                'string',
                'max:120',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('pages', 'slug')->where('locale_id', $this->integer('locale_id')),
            ],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string', 'max:20000'],
            'content_readiness' => ['required', Rule::in(array_keys($store->contentReadinessOptions()))],
            'featured_media_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'page_type' => ['required', Rule::in(array_keys($store->pageTypes()))],
            'status' => ['required', Rule::in(array_keys($store->statuses()))],
            'intent' => ['nullable', Rule::in(['save_draft', 'publish', 'hide'])],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
