<?php

namespace App\Services\Abuse;

use App\Models\AbuseReport;
use App\Services\Security\SecuritySettingsStore;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AbuseReportStore
{
    public function __construct(
        private readonly AbuseCaseReferenceService $references,
        private readonly SecuritySettingsStore $security,
    ) {}

    /** @param array<string, mixed> $payload */
    public function create(array $payload, Request $request): AbuseReport
    {
        $description = $this->sanitize((string) $payload['description']);
        $email = strtolower((string) $payload['reporter_email']);

        return AbuseReport::query()->create([
            'case_reference' => $this->references->generate(),
            'report_type' => $payload['report_type'],
            'priority' => $payload['priority'] ?? 'normal',
            'status' => 'new',
            'reporter_name' => trim((string) $payload['reporter_name']),
            'reporter_email' => $email,
            'reporter_email_hash' => hash('sha256', $email),
            'subject' => trim(strip_tags((string) $payload['subject'])),
            'description' => $description,
            'description_excerpt' => Str::limit(strip_tags($description), 220, ''),
            'reported_mailbox_id' => $payload['reported_mailbox_id'] ?? null,
            'reported_domain_id' => $payload['reported_domain_id'] ?? null,
            'reported_user_id' => $payload['reported_user_id'] ?? null,
            'related_url' => $payload['related_url'] ?? null,
            'evidence_media_ids' => array_values($payload['evidence_media_ids'] ?? []),
            'submitted_ip_hash' => filled($request->ip()) ? hash('sha256', (string) $request->ip()) : null,
            'bot_protection_readiness' => $this->botReadiness(),
            'reporter_notification_status' => 'ready',
        ]);
    }

    private function sanitize(string $value): string
    {
        if (preg_match('/(<\?php|<\?=|@php|@endphp|{{|}}|{!!|!!}|<\s*script\b|\son[a-z]+\s*=|javascript:|data:text\/html)/i', $value) === 1) {
            throw ValidationException::withMessages(['description' => 'Reports cannot include executable content.']);
        }

        return trim(strip_tags($value));
    }

    /** @return array<string, mixed> */
    private function botReadiness(): array
    {
        $bot = $this->security->bot();
        $protected = in_array('contact_form', $bot['protected_forms'] ?? [], true);

        return [
            'provider' => $bot['provider'] ?? null,
            'active' => (bool) ($bot['is_active'] ?? false),
            'protected' => $protected,
            'status' => ($bot['is_active'] ?? false) && $protected ? 'protected' : 'readiness',
        ];
    }
}
