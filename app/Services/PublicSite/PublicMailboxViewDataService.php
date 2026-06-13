<?php

namespace App\Services\PublicSite;

use App\Actions\PublicSite\RefreshPublicInboxAction;
use App\Models\Locale;
use App\Models\Mailbox;
use App\Models\MailboxMessage;
use App\Services\Mailboxes\MailboxMessageService;

class PublicMailboxViewDataService
{
    public function __construct(
        private readonly PublicViewDataService $base,
        private readonly PublicMailboxAccessService $access,
        private readonly RefreshPublicInboxAction $refresh,
        private readonly MailboxMessageService $messages,
    ) {}

    /** @param array<string, mixed> $theme */
    public function show(Mailbox $mailbox, Locale $locale, array $theme, string $token, ?MailboxMessage $selected = null): array
    {
        $mailbox = $this->refresh->handle($mailbox);
        $mailbox->loadMissing('domain');
        $messageList = $this->messages->list($mailbox, 20);

        if ($selected && ! $this->messages->belongsTo($mailbox, $selected)) {
            abort(404);
        }

        $data = $this->base->home($locale, $theme);

        return [
            ...$data,
            'mailbox' => [
                'id' => $mailbox->id,
                'address' => $mailbox->address,
                'status' => $mailbox->status,
                'expired' => $mailbox->status !== 'active',
                'expires_at' => $mailbox->expires_at?->toIso8601String(),
                'seconds_remaining' => $mailbox->expires_at ? max(0, now()->diffInSeconds($mailbox->expires_at, false)) : null,
                'expires_label' => $this->expiresLabel($mailbox),
                'refresh_action' => route('public.mailbox.refresh', ['locale' => $locale->locale, 'mailbox' => $mailbox]),
                'current_url' => $this->access->url($mailbox, $locale->locale, $token),
                'access_token' => $token,
                'messages' => $messageList->getCollection()
                    ->map(fn (MailboxMessage $message): array => $this->messageSummary($mailbox, $message, $locale->locale, $token))
                    ->values()
                    ->all(),
                'selected_message' => $selected ? $this->messagePreview($selected) : null,
            ],
        ];
    }

    private function messageSummary(Mailbox $mailbox, MailboxMessage $message, string $locale, string $token): array
    {
        return [
            'id' => $message->id,
            'sender' => $message->sender_name ?: $message->sender_email,
            'subject' => $message->subject ?: '(no subject)',
            'preview' => $message->preview_text,
            'received_at' => $message->received_at?->diffForHumans(),
            'unread' => $message->isUnread(),
            'url' => $this->access->messageUrl($mailbox, $message->id, $locale, $token),
        ];
    }

    private function messagePreview(MailboxMessage $message): array
    {
        return [
            'sender' => $message->sender_name ?: $message->sender_email,
            'subject' => $message->subject ?: '(no subject)',
            'received_at' => $message->received_at?->toDayDateTimeString(),
            'body_html' => $message->sanitized_html_body,
            'body_text' => $message->plain_text_body,
            'attachment_count' => $message->attachment_count,
        ];
    }

    private function expiresLabel(Mailbox $mailbox): string
    {
        if (! $mailbox->expires_at) {
            return 'No expiration';
        }

        $seconds = max(0, (int) now()->diffInSeconds($mailbox->expires_at, false));

        if ($seconds === 0) {
            return 'Expired';
        }

        $days = intdiv($seconds, 86400);
        $hours = intdiv($seconds % 86400, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        if ($days > 0) {
            return "{$days}d {$hours}h";
        }

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return max(1, $minutes).'m';
    }
}
