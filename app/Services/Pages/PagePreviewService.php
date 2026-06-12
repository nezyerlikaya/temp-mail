<?php

namespace App\Services\Pages;

use App\Models\Page;
use Illuminate\Support\Facades\URL;

class PagePreviewService
{
    public function previewUrl(Page $page): string
    {
        return URL::temporarySignedRoute('admin.page-studio.preview', now()->addMinutes(30), $page);
    }

    public function publicUrl(Page $page): string
    {
        $locale = $page->locale?->locale ?? 'en';

        return url('/'.$locale.'/'.$page->slug);
    }

    /** @return array<string, mixed> */
    public function readiness(Page $page): array
    {
        return [
            'preview_url' => $this->previewUrl($page),
            'public_url' => $this->publicUrl($page),
            'signed' => true,
            'expires_in' => '30 minutes',
            'public_ready' => $page->status === 'published',
        ];
    }
}
