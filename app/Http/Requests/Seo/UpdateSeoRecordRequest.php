<?php

namespace App\Http\Requests\Seo;

use App\Services\Seo\SeoStore;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSeoRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.seo-growth-center.update') ?? false;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'meta_title' => ['nullable', 'string', 'max:180'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'canonical_url' => ['nullable', 'string', 'max:500'],
            'robots_index' => ['required', 'boolean'],
            'robots_follow' => ['required', 'boolean'],
            'include_in_sitemap' => ['required', 'boolean'],
            'sitemap_priority' => ['required', 'numeric', 'min:0', 'max:1'],
            'sitemap_change_frequency' => ['required', Rule::in(array_keys(app(SeoStore::class)->changeFrequencies()))],
            'og_title' => ['nullable', 'string', 'max:180'],
            'og_description' => ['nullable', 'string', 'max:320'],
            'og_image_media_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'twitter_card' => ['required', Rule::in(array_keys(app(SeoStore::class)->twitterCards()))],
            'schema_type' => ['nullable', 'string', 'max:64'],
            'schema_json' => ['nullable', 'array'],
        ];
    }
}
