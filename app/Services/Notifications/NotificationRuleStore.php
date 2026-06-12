<?php

namespace App\Services\Notifications;

use App\Models\NotificationRule;
use Illuminate\Support\Collection;

class NotificationRuleStore
{
    /** @return Collection<int, NotificationRule> */
    public function all(): Collection
    {
        $this->ensureDefaults();

        return NotificationRule::query()->orderByRaw($this->eventOrderSql())->get();
    }

    /** @return array<string, array<string, mixed>> */
    public function defaults(): array
    {
        return [
            'new_pending_comment' => $this->rule('info', ['admin', 'editor', 'moderator'], true, false, 'immediate', 'content'),
            'spam_comment_detected' => $this->rule('warning', ['admin', 'moderator'], true, false, 'immediate', 'content'),
            'failed_admin_login' => $this->rule('critical', ['owner', 'admin'], true, true, 'immediate', 'trust'),
            'new_abuse_report' => $this->rule('warning', ['owner', 'admin', 'moderator'], true, true, 'immediate', 'trust'),
            'domain_health_failed' => $this->rule('warning', ['owner', 'admin'], true, true, 'daily', 'mail-infrastructure'),
            'smtp_imap_test_failed' => $this->rule('warning', ['owner', 'admin'], true, true, 'daily', 'mail-infrastructure'),
            'update_available' => $this->rule('info', ['owner', 'admin'], true, false, 'daily', 'system'),
            'premium_expiring_soon' => $this->rule('warning', ['owner', 'admin'], true, true, 'daily', 'billing'),
            'security_setting_changed' => $this->rule('critical', ['owner', 'admin'], true, true, 'immediate', 'trust'),
            'backup_failed' => $this->rule('critical', ['owner', 'admin'], true, true, 'immediate', 'system'),
        ];
    }

    public function ensureDefaults(): void
    {
        foreach ($this->defaults() as $eventKey => $defaults) {
            NotificationRule::query()->firstOrCreate(['event_key' => $eventKey], [
                'severity' => $defaults['severity'],
                'in_app_enabled' => $defaults['in_app_enabled'],
                'email_enabled' => $defaults['email_enabled'],
                'recipient_roles' => $defaults['recipient_roles'],
                'digest_mode' => $defaults['digest_mode'],
                'quiet_hours_enabled' => false,
                'quiet_hours_start' => null,
                'quiet_hours_end' => null,
                'is_active' => true,
            ]);
        }
    }

    /** @return array<string, string> */
    public function labels(): array
    {
        return [
            'new_pending_comment' => 'New pending comment',
            'spam_comment_detected' => 'Spam comment detected',
            'failed_admin_login' => 'Failed admin login',
            'new_abuse_report' => 'New abuse report',
            'domain_health_failed' => 'Domain health failed',
            'smtp_imap_test_failed' => 'SMTP/IMAP test failed',
            'update_available' => 'Update available',
            'premium_expiring_soon' => 'Premium expiring soon',
            'security_setting_changed' => 'Security setting changed',
            'backup_failed' => 'Backup failed',
        ];
    }

    /** @return array<string, string> */
    public function modules(): array
    {
        return collect($this->defaults())->mapWithKeys(fn (array $rule, string $eventKey): array => [
            $eventKey => $rule['module'],
        ])->all();
    }

    /** @return array<int, string> */
    public function roleOptions(): array
    {
        return ['owner', 'admin', 'editor', 'moderator'];
    }

    /** @return array<string, mixed> */
    private function rule(string $severity, array $roles, bool $inApp, bool $email, string $digest, string $module): array
    {
        return [
            'severity' => $severity,
            'recipient_roles' => $roles,
            'in_app_enabled' => $inApp,
            'email_enabled' => $email,
            'digest_mode' => $digest,
            'module' => $module,
        ];
    }

    private function eventOrderSql(): string
    {
        $keys = array_keys($this->defaults());
        $cases = collect($keys)->map(fn (string $key, int $index): string => "WHEN event_key = '".$key."' THEN ".$index)->join(' ');

        return 'CASE '.$cases.' ELSE 999 END';
    }
}
