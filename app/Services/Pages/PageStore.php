<?php

namespace App\Services\Pages;

use App\Models\Locale;
use App\Models\Page;
use App\Models\User;
use App\Services\Localization\LocaleSettingsStore;
use Illuminate\Database\Eloquent\Collection;

class PageStore
{
    public function __construct(private readonly LocaleSettingsStore $locales) {}

    /** @return array<string, string> */
    public function pageTypes(): array
    {
        return [
            'home_readiness' => 'Home readiness',
            'privacy_policy' => 'Privacy Policy',
            'terms_of_service' => 'Terms of Service',
            'cookie_policy' => 'Cookie Policy',
            'contact' => 'Contact',
            'abuse' => 'Abuse',
            'dmca' => 'DMCA',
            'faq_readiness' => 'FAQ page readiness',
            'pricing_readiness' => 'Pricing readiness',
            'api_docs_readiness' => 'API Docs readiness',
        ];
    }

    /** @return array<string, string> */
    public function statuses(): array
    {
        return [
            'draft' => 'Draft',
            'hidden' => 'Hidden',
            'published' => 'Published',
        ];
    }

    /** @return array<string, string> */
    public function contentReadinessOptions(): array
    {
        return [
            'outline' => 'Outline',
            'needs_content' => 'Needs content',
            'needs_review' => 'Needs review',
            'ready' => 'Ready',
        ];
    }

    /** @return Collection<int, Locale> */
    public function locales(): Collection
    {
        return $this->locales->all();
    }

    /** @return Collection<int, User> */
    public function authors(): Collection
    {
        return User::query()
            ->whereIn('id', Page::query()->select('author_id')->whereNotNull('author_id'))
            ->orderBy('name')
            ->get();
    }

    /** @return array{total: int, draft: int, published: int, ready: int, legal: int} */
    public function summary(): array
    {
        return [
            'total' => Page::query()->count(),
            'draft' => Page::query()->where('status', 'draft')->count(),
            'published' => Page::query()->where('status', 'published')->count(),
            'ready' => Page::query()->where('content_readiness', 'ready')->count(),
            'legal' => Page::query()->whereIn('page_type', ['privacy_policy', 'terms_of_service', 'cookie_policy', 'dmca'])->count(),
        ];
    }

    /** @return Collection<int, Page> */
    public function recent(int $limit = 5): Collection
    {
        return Page::query()
            ->with(['locale', 'author'])
            ->latest()
            ->limit($limit)
            ->get();
    }
}
