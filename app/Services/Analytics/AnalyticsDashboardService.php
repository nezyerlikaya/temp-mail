<?php

namespace App\Services\Analytics;

use App\Models\AbuseSignal;
use App\Models\Domain;
use App\Models\Mailbox;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Support\Carbon;

class AnalyticsDashboardService
{
    public function __construct(
        private readonly AnalyticsQueryService $query,
        private readonly AnalyticsFreshnessService $freshness,
    ) {}

    /** @param array{from: Carbon, to: Carbon, label: string} $range @param array<string, mixed> $filters @return array<string, mixed> */
    public function dashboard(array $range, array $filters): array
    {
        $mailboxesCreated = $this->query->total('mailbox.created', $range, $filters);
        $emailsReceived = $this->query->total('mailbox.email_received', $range, $filters);
        $registeredUsers = $this->query->total('user.registered', $range, $filters);
        $premiumGranted = $this->query->total('premium.granted', $range, $filters);
        $blogViews = $this->query->total('blog.viewed', $range, $filters);

        return [
            'kpis' => [
                ['label' => 'Visitors', 'value' => $this->query->visitors($range, $filters), 'detail' => 'Anonymized aggregate visitors'],
                ['label' => 'Mailboxes created', 'value' => $mailboxesCreated, 'detail' => 'Tracked from aggregate events'],
                ['label' => 'Emails received', 'value' => $emailsReceived, 'detail' => 'Message body content excluded'],
                ['label' => 'Active inboxes', 'value' => $this->mailboxCount('active', $filters), 'detail' => 'Current operational state'],
                ['label' => 'Expired inboxes', 'value' => $this->mailboxCount('expired', $filters), 'detail' => 'Current lifecycle state'],
                ['label' => 'Registered users', 'value' => $registeredUsers, 'detail' => 'Aggregate registration events'],
                ['label' => 'Premium users', 'value' => $this->premiumUsers(), 'detail' => 'Current active/expiring memberships'],
                ['label' => 'Conversion rate', 'value' => $this->percent($premiumGranted, max(1, $registeredUsers)), 'detail' => 'Premium grants over registrations'],
                ['label' => 'Top domains', 'value' => count($this->query->topDomains($range, $filters)), 'detail' => 'Domains with aggregate activity'],
                ['label' => 'Top languages', 'value' => count($this->query->topLanguages($range, $filters)), 'detail' => 'Locales with aggregate activity'],
                ['label' => 'Blog views', 'value' => $blogViews, 'detail' => 'Aggregate content view events'],
            ],
            'sections' => [
                'mailbox' => [
                    'mailboxes_created' => $this->query->trend(['mailbox.created'], $range, $filters),
                    'emails_received' => $this->query->trend(['mailbox.email_received'], $range, $filters),
                    'inbox_lifecycle' => $this->query->trend(['mailbox.created', 'mailbox.expired'], $range, $filters),
                ],
                'domains' => [
                    'mailboxes' => $this->query->topDomains($range, $filters, 'mailbox.created'),
                    'emails' => $this->query->topDomains($range, $filters, 'mailbox.email_received'),
                    'health' => $this->domainHealth(),
                ],
                'conversion' => [
                    'guest_to_registered' => $this->percent($registeredUsers, max(1, $mailboxesCreated)),
                    'registered_to_premium' => $this->percent($premiumGranted, max(1, $registeredUsers)),
                    'premium_expiring' => Membership::query()->where('status', 'expiring')->count(),
                ],
                'content' => [
                    'blog_views' => $this->query->trend(['blog.viewed'], $range, $filters),
                    'top_languages' => $this->query->topLanguages($range, $filters),
                    'seo_landing_pages' => 'Readiness only',
                ],
                'security' => [
                    'rate_limited' => $this->query->total('security.rate_limited', $range, $filters),
                    'failed_logins' => AbuseSignal::query()->where('signal_type', 'failed_admin_login')->count(),
                    'blocked_comments' => AbuseSignal::query()->where('signal_type', 'spam_blocked')->count(),
                    'spam_comments' => AbuseSignal::query()->where('signal_type', 'suspicious_comment')->count(),
                ],
            ],
            'freshness' => $this->freshness->status(),
        ];
    }

    /** @param array<string, mixed> $filters */
    private function mailboxCount(string $status, array $filters): int
    {
        return Mailbox::query()
            ->where('status', $status)
            ->when(($filters['domain_id'] ?? 'all') !== 'all', fn ($query) => $query->where('domain_id', $filters['domain_id']))
            ->when(($filters['locale_id'] ?? 'all') !== 'all', fn ($query) => $query->where('locale_id', $filters['locale_id']))
            ->count();
    }

    private function premiumUsers(): int
    {
        return User::query()
            ->whereIn('membership_status', ['active', 'expiring'])
            ->whereIn('current_plan_reference', ['premium', 'business'])
            ->count();
    }

    /** @return array<int, array{label: string, value: int}> */
    private function domainHealth(): array
    {
        return [
            ['label' => 'Active public', 'value' => Domain::query()->where('is_active', true)->where('is_public', true)->count()],
            ['label' => 'DNS ready', 'value' => Domain::query()->where('catch_all_ready', true)->count()],
        ];
    }

    private function percent(int $part, int $total): string
    {
        return number_format(($part / max(1, $total)) * 100, 1).'%';
    }
}
