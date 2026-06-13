<?php

namespace App\Services\PublicSite;

use App\Models\Locale;
use App\Services\Appearance\AppearanceTokenResolver;
use App\Services\Typography\FontStackResolver;
use Throwable;

class PublicViewDataService
{
    private const TRANSLATION_KEYS = [
        'common.brand.name',
        'nav.home',
        'nav.blog',
        'blog.index.title',
        'blog.index.description',
        'blog.empty.title',
        'blog.empty.body',
        'blog.read_more',
        'blog.related.title',
        'blog.comments.title',
        'blog.comments.name',
        'blog.comments.email',
        'blog.comments.content',
        'blog.comments.submit',
        'blog.comments.closed',
        'page.updated',
        'home.header.logo',
        'home.hero.title',
        'home.hero.description',
        'home.cta.title',
        'home.cta.button',
        'home.badge.no_permanent_inbox',
        'home.badge.privacy_first',
        'home.visual.mailbox_stream',
        'home.visual.ready',
        'home.feature.simple.title',
        'home.feature.simple.body',
        'home.feature.private.title',
        'home.feature.private.body',
        'home.feature.locale.body',
        'mailbox.create.button',
        'mailbox.create.title',
        'mailbox.create.description',
        'mailbox.domain.empty',
        'mailbox.domain.label',
        'mailbox.alias.label',
        'mailbox.alias.placeholder',
        'mailbox.empty.title',
        'mailbox.empty.body',
        'mailbox.refresh.label',
        'mailbox.status.active',
        'mailbox.status.expired',
        'mailbox.expires.label',
        'mailbox.messages.title',
        'mailbox.preview.title',
        'mailbox.preview.empty',
        'footer.copyright',
    ];

    public function __construct(
        private readonly PublicTranslationResolver $translations,
        private readonly PublicNavigationService $navigation,
        private readonly PublicBrandResolver $brand,
        private readonly PublicSeoResolver $seo,
        private readonly PublicLegalNavigationService $legal,
        private readonly AppearanceTokenResolver $appearance,
        private readonly FontStackResolver $typography,
    ) {}

    /** @param array<string, mixed> $theme */
    public function home(Locale $locale, array $theme): array
    {
        $translations = $this->translations->resolve($locale, self::TRANSLATION_KEYS);
        $brand = $this->brand->resolve();

        return $this->base($locale, $theme, $translations, $brand);
    }

    /**
     * @param  array<string, mixed>  $theme
     * @param  array<string, mixed>  $seo
     * @param  array<int, array<string, mixed>>|null  $localeSwitcher
     */
    public function content(Locale $locale, array $theme, array $seo, string $navigation = 'home', ?array $localeSwitcher = null): array
    {
        $translations = $this->translations->resolve($locale, self::TRANSLATION_KEYS);
        $brand = $this->brand->resolve();

        return [
            ...$this->base($locale, $theme, $translations, $brand, $navigation, $localeSwitcher),
            'seo' => $seo,
        ];
    }

    /**
     * @param  array<string, mixed>  $theme
     * @param  array<string, string>  $translations
     * @param  array<string, mixed>  $brand
     * @param  array<int, array<string, mixed>>|null  $localeSwitcher
     */
    private function base(Locale $locale, array $theme, array $translations, array $brand, string $navigation = 'home', ?array $localeSwitcher = null): array
    {
        $appearance = $this->safeAppearance($theme['slug']);
        $typography = $this->safeTypography($theme['slug'], $locale->locale);

        return [
            'theme' => $theme,
            'locale' => [
                'code' => $locale->locale,
                'name' => $locale->language_name,
                'native_name' => $locale->native_name,
                'direction' => $locale->direction === 'rtl' ? 'rtl' : 'ltr',
            ],
            'translations' => $translations,
            'navigation' => $this->navigation->resolve($locale, $translations, $navigation, $localeSwitcher),
            'brand' => $brand,
            'legal_links' => $this->legal->links($locale->id, $locale->locale),
            'seo' => $this->seo->home($locale, $brand),
            'appearance' => $appearance,
            'typography' => $typography,
            'style' => $this->style($appearance, $typography),
            'current_year' => now()->year,
        ];
    }

    private function safeAppearance(string $theme): array
    {
        try {
            return $this->appearance->forTheme($theme);
        } catch (Throwable) {
            return ['inline_style' => ''];
        }
    }

    private function safeTypography(string $theme, string $locale): array
    {
        try {
            return $this->typography->resolve($theme, $locale);
        } catch (Throwable) {
            return ['inline_style' => ''];
        }
    }

    /**
     * @param  array<string, mixed>  $appearance
     * @param  array<string, mixed>  $typography
     */
    private function style(array $appearance, array $typography): string
    {
        return collect([
            $appearance['inline_style'] ?? '',
            $typography['inline_style'] ?? '',
            'font-family: var(--tm-font-body, Plus Jakarta Sans, ui-sans-serif, system-ui);',
        ])
            ->map(fn (string $chunk): string => trim($chunk))
            ->filter()
            ->map(fn (string $chunk): string => str_ends_with($chunk, ';') ? $chunk : $chunk.';')
            ->implode(' ');
    }
}
