<?php

namespace App\Services\Notifications;

use App\Models\EmailTemplate;
use App\Models\Locale;
use App\Models\SystemNotification;
use App\Services\EmailTemplates\EmailTemplateRenderer;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Throwable;

class EmailNotificationDispatcher
{
    public function __construct(private readonly EmailTemplateRenderer $renderer) {}

    public function dispatch(SystemNotification $notification): bool
    {
        $notification->forceFill([
            'email_attempted_at' => now(),
            'email_status' => 'skipped',
        ])->save();

        $template = $this->templateFor($notification);

        if ($template === null || ! $this->mailIsReady()) {
            return false;
        }

        try {
            $values = $this->values($notification);
            $subject = $this->renderSubject($template->subject, $values);
            $html = $this->renderer->renderHtml($template, $values);

            Mail::html($html, function ($message) use ($notification, $subject): void {
                $message->to($notification->recipient->email)->subject($subject);
            });

            $notification->forceFill([
                'email_sent_at' => now(),
                'email_status' => 'sent',
            ])->save();

            return true;
        } catch (Throwable) {
            $notification->forceFill(['email_status' => 'failed'])->save();

            return false;
        }
    }

    private function templateFor(SystemNotification $notification): ?EmailTemplate
    {
        if (! Schema::hasTable('email_templates') || ! Schema::hasTable('locales')) {
            return null;
        }

        $localeId = Locale::query()
            ->where('locale', $notification->recipient->language_preference ?: 'en')
            ->value('id')
            ?? Locale::query()->where('is_default', true)->value('id')
            ?? Locale::query()->value('id');

        if ($localeId === null) {
            return null;
        }

        return EmailTemplate::query()
            ->where('locale_id', $localeId)
            ->where('template_key', $this->templateKey($notification->event_key))
            ->where('status', 'active')
            ->first();
    }

    private function mailIsReady(): bool
    {
        return filled(config('mail.default')) && filled(config('mail.from.address'));
    }

    private function templateKey(string $eventKey): string
    {
        return match ($eventKey) {
            'premium_expiring_soon' => 'premium_expiring',
            'update_available' => 'update_available',
            'backup_failed' => 'backup_failed',
            'new_abuse_report' => 'abuse_report_received',
            'failed_admin_login', 'security_setting_changed' => 'security_alert',
            'new_pending_comment' => 'comment_pending_notification',
            default => 'security_alert',
        };
    }

    /** @return array<string, string> */
    private function values(SystemNotification $notification): array
    {
        return [
            'app_name' => config('app.name'),
            'user_name' => $notification->recipient->name,
            'login_url' => route('login'),
            'support_email' => (string) config('mail.from.address'),
            'abuse_email' => (string) config('mail.from.address'),
            'premium_ends_at' => '',
            'reset_url' => '',
            'verification_url' => '',
        ];
    }

    /** @param array<string, string> $values */
    private function renderSubject(string $subject, array $values): string
    {
        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', fn (array $match): string => $values[$match[1]] ?? '', $subject) ?? $subject;
    }
}
