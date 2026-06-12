<?php

namespace App\Services\EmailTemplates;

use App\Models\EmailTemplate;
use App\Models\Locale;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EmailTemplateStore
{
    /** @param array<string, mixed> $filters */
    public function search(array $filters): LengthAwarePaginator
    {
        return EmailTemplate::query()
            ->with(['locale', 'updater'])
            ->when(($filters['locale_id'] ?? 'all') !== 'all', fn ($query) => $query->where('locale_id', (int) $filters['locale_id']))
            ->when(($filters['template_key'] ?? 'all') !== 'all', fn ($query) => $query->where('template_key', (string) $filters['template_key']))
            ->when(($filters['status'] ?? 'all') !== 'all', fn ($query) => $query->where('status', (string) $filters['status']))
            ->latest('updated_at')
            ->paginate(12)
            ->withQueryString();
    }

    /** @return Collection<int, Locale> */
    public function locales(): Collection
    {
        return Locale::query()->orderByDesc('is_default')->orderBy('sort_order')->orderBy('language_name')->get();
    }

    /** @return array<string, string> */
    public function templateKeys(): array
    {
        return [
            'password_reset' => 'Password reset',
            'email_verification' => 'Email verification',
            'admin_invite' => 'Admin invite',
            'login_alert' => 'Login alert',
            'premium_expiring' => 'Premium expiring',
            'premium_expired' => 'Premium expired',
            'comment_pending_notification' => 'Comment pending notification',
            'security_alert' => 'Security alert',
            'update_available' => 'Update available',
            'backup_failed' => 'Backup failed',
            'abuse_report_received' => 'Abuse report received',
            'contact_form_received' => 'Contact form received',
        ];
    }

    /** @return array<string, string> */
    public function statuses(): array
    {
        return [
            'draft' => 'Draft',
            'active' => 'Active',
            'hidden' => 'Hidden',
        ];
    }

    /** @return array<string, int> */
    public function summary(): array
    {
        $expected = $this->locales()->count() * count($this->templateKeys());

        return [
            'expected' => $expected,
            'records' => EmailTemplate::query()->count(),
            'active' => EmailTemplate::query()->where('status', 'active')->count(),
            'draft' => EmailTemplate::query()->where('status', 'draft')->count(),
            'missing' => max(0, $expected - EmailTemplate::query()->count()),
        ];
    }

    /** @return Collection<int, array<string, mixed>> */
    public function missingQueue(): Collection
    {
        $existing = EmailTemplate::query()->get(['locale_id', 'template_key'])
            ->map(fn (EmailTemplate $template): string => $template->locale_id.'|'.$template->template_key)
            ->flip();

        return $this->locales()->flatMap(function (Locale $locale) use ($existing): array {
            return collect($this->templateKeys())
                ->reject(fn (string $label, string $key): bool => $existing->has($locale->id.'|'.$key))
                ->map(fn (string $label, string $key): array => [
                    'locale' => $locale,
                    'template_key' => $key,
                    'label' => $label,
                ])
                ->all();
        })->values();
    }
}
