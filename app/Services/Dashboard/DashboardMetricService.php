<?php

namespace App\Services\Dashboard;

use App\Models\AbuseSignal;
use App\Models\Domain;
use App\Models\InboundMailConnection;
use App\Models\Locale;
use App\Models\Mailbox;
use App\Models\MailboxMessage;
use App\Models\Membership;
use App\Models\SmtpConnection;
use App\Models\SystemBackup;
use App\Models\SystemHealthCheck;
use App\Models\SystemNotification;
use App\Models\UpdateCheck;
use App\Models\UserAuditEvent;
use Illuminate\Support\Facades\Schema;
use Throwable;

class DashboardMetricService
{
    /** @return array<int, array<string, mixed>> */
    public function metrics(bool $includeSensitive = true): array
    {
        $metrics = [
            $this->metric('active_inboxes', 'Active inboxes', $this->count('mailboxes', fn (): int => Mailbox::query()->where('status', 'active')->count()), 'Currently usable mailboxes.', 'inbox', 'neutral'),
            $this->metric('mailboxes_today', 'Mailboxes today', $this->count('mailboxes', fn (): int => Mailbox::query()->whereDate('created_at', today())->count()), 'New mailbox records created today.', 'mail-plus', 'neutral'),
            $this->metric('emails_today', 'Emails today', $this->count('mailbox_messages', fn (): int => MailboxMessage::query()->whereDate('received_at', today())->count()), 'Messages received today.', 'mail-check', 'neutral'),
            $this->metric('pending_comments', 'Pending comments', $this->pendingComments(), 'Comment moderation readiness signals.', 'message-square-warning', 'attention'),
            $this->metric('domain_health', 'Domain health', $this->domainHealthLabel(), 'DNS and catch-all readiness.', 'globe-2', $this->domainHealthTone()),
            $this->metric('imap_smtp_health', 'IMAP/SMTP health', $this->mailHealthLabel(), 'Inbound and SMTP connection readiness.', 'server-cog', $this->mailHealthTone()),
            $this->metric('update_availability', 'Update availability', $this->updateLabel(), 'Latest update check result.', 'package-check', 'neutral'),
            $this->metric('latest_backup', 'Latest backup', $this->backupLabel(), 'Most recent backup status.', 'database-backup', 'neutral'),
            $this->metric('system_health', 'System health', $this->systemHealthLabel(), 'Last system health run.', 'activity', $this->systemHealthTone()),
            $this->metric('premium_memberships', 'Active Premium', $this->activePremiumMemberships(), 'Active paid membership grants.', 'badge-check', 'neutral'),
            $this->metric('locale_attention', 'Locale attention', $this->localesRequiringAttention(), 'Markets not ready for launch.', 'languages', 'attention'),
        ];

        if ($includeSensitive) {
            $metrics[] = $this->metric('abuse_alerts', 'Abuse alerts', $this->count('abuse_signals', fn (): int => AbuseSignal::query()->where('status', 'open')->count()), 'Open abuse and spam signals.', 'shield-alert', 'critical');
            $metrics[] = $this->metric('failed_admin_logins', 'Failed admin logins', $this->failedAdminLogins(), 'Failed login events recorded today.', 'lock-keyhole', 'critical');
        }

        return $metrics;
    }

    /** @return array<string, mixed> */
    private function metric(string $key, string $label, int|string $value, string $detail, string $icon, string $tone): array
    {
        return compact('key', 'label', 'value', 'detail', 'icon', 'tone');
    }

    private function pendingComments(): int
    {
        return $this->count('system_notifications', fn (): int => SystemNotification::query()
            ->where('event_key', 'new_pending_comment')
            ->whereNull('archived_at')
            ->count());
    }

    private function failedAdminLogins(): int
    {
        return $this->count('user_audit_events', fn (): int => UserAuditEvent::query()
            ->whereIn('event', ['auth.failed', 'failed_admin_login', 'login.failed'])
            ->whereDate('created_at', today())
            ->count());
    }

    private function activePremiumMemberships(): int
    {
        return $this->count('memberships', fn (): int => Membership::query()
            ->where('status', 'active')
            ->where(function ($query): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->whereHas('plan', fn ($query) => $query->where('key', 'premium'))
            ->count());
    }

    private function localesRequiringAttention(): int
    {
        return $this->count('locales', fn (): int => Locale::query()
            ->where(fn ($query) => $query
                ->where('market_readiness', '!=', 'ready')
                ->orWhere('launch_status', '!=', 'published'))
            ->count());
    }

    private function domainHealthLabel(): string
    {
        $total = $this->count('domains', fn (): int => Domain::query()->count());
        $healthy = $this->count('domains', fn (): int => Domain::query()->where('status', 'ready')->count());

        return $total === 0 ? 'No domains' : $healthy.'/'.$total.' ready';
    }

    private function domainHealthTone(): string
    {
        return str_starts_with($this->domainHealthLabel(), 'No') || str_starts_with($this->domainHealthLabel(), '0/') ? 'attention' : 'neutral';
    }

    private function mailHealthLabel(): string
    {
        $inbound = $this->count('inbound_mail_connections', fn (): int => InboundMailConnection::query()->where('is_active', true)->where('status', 'connected')->count());
        $smtp = $this->count('smtp_connections', fn (): int => SmtpConnection::query()->where('is_active', true)->where('status', 'connected')->count());

        return $inbound.' inbound / '.$smtp.' SMTP';
    }

    private function mailHealthTone(): string
    {
        return $this->mailHealthLabel() === '0 inbound / 0 SMTP' ? 'attention' : 'neutral';
    }

    private function updateLabel(): string
    {
        return $this->value('update_checks', function (): string {
            $check = UpdateCheck::query()->latest('checked_at')->latest()->first();

            return $check?->status ? str($check->status)->replace('_', ' ')->headline()->toString() : 'Not checked';
        }, 'Unavailable');
    }

    private function backupLabel(): string
    {
        return $this->value('system_backups', function (): string {
            $backup = SystemBackup::query()->latest('created_at')->first();

            return $backup?->status ? str($backup->status)->headline()->toString() : 'No backups';
        }, 'Unavailable');
    }

    private function systemHealthLabel(): string
    {
        return $this->value('system_health_checks', function (): string {
            $health = SystemHealthCheck::query()->latest('checked_at')->first();

            return $health?->overall_status ? str($health->overall_status)->headline()->toString() : 'Not checked';
        }, 'Unavailable');
    }

    private function systemHealthTone(): string
    {
        return in_array($this->systemHealthLabel(), ['Critical', 'Warning', 'Not checked', 'Unavailable'], true) ? 'attention' : 'neutral';
    }

    private function count(string $table, callable $callback): int
    {
        return (int) $this->value($table, $callback, 0);
    }

    private function value(string $table, callable $callback, mixed $fallback): mixed
    {
        try {
            if (! Schema::hasTable($table)) {
                return $fallback;
            }

            return $callback();
        } catch (Throwable) {
            return $fallback;
        }
    }
}
