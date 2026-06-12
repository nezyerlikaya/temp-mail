<?php

namespace App\Services\Seo;

class SeoTemplateVariableRegistry
{
    /** @return array<string, string> */
    public function variables(): array
    {
        return [
            'page_title' => 'Page title',
            'post_title' => 'Post title',
            'category_name' => 'Category name',
            'tag_name' => 'Tag name',
            'locale_name' => 'Locale name',
            'site_name' => 'Site name',
        ];
    }

    /** @return array<int, string> */
    public function invalidVariables(?string $template): array
    {
        preg_match_all('/\{([a-zA-Z0-9_]+)\}/', (string) $template, $matches);

        return collect($matches[1] ?? [])
            ->reject(fn (string $variable): bool => array_key_exists($variable, $this->variables()))
            ->values()
            ->all();
    }

    /** @param array<string, string> $values */
    public function render(?string $template, array $values): ?string
    {
        if (blank($template)) {
            return null;
        }

        return preg_replace_callback('/\{([a-zA-Z0-9_]+)\}/', function (array $match) use ($values): string {
            return $values[$match[1]] ?? '';
        }, (string) $template);
    }
}
