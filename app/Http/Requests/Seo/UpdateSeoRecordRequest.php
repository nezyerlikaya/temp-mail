<?php

namespace App\Http\Requests\Seo;

use App\Services\Seo\SeoCanonicalValidator;
use App\Services\Seo\SeoSchemaValidator;
use App\Services\Seo\SeoStore;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSeoRecordRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        foreach (['robots_index', 'robots_follow', 'include_in_sitemap'] as $field) {
            if (! $this->has($field)) {
                $this->merge([$field => false]);
            }
        }
    }

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
            'twitter_title' => ['nullable', 'string', 'max:180'],
            'twitter_description' => ['nullable', 'string', 'max:320'],
            'twitter_image_media_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'schema_type' => ['nullable', 'string', 'max:64'],
            'schema_json_text' => ['nullable', 'string', 'max:12000'],
            'breadcrumb_title' => ['nullable', 'string', 'max:120'],
        ];
    }

    /** @return array<string, mixed> */
    public function validated($key = null, $default = null)
    {
        $payload = parent::validated($key, $default);

        if ($key !== null) {
            return $payload;
        }

        app(SeoCanonicalValidator::class)->validate($payload['canonical_url'] ?? null);
        $payload['schema_json'] = app(SeoSchemaValidator::class)->sanitize($payload['schema_json_text'] ?? null);
        unset($payload['schema_json_text']);

        return $payload;
    }
}
