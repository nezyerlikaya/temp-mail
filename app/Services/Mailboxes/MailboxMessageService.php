<?php

namespace App\Services\Mailboxes;

use App\Models\Mailbox;
use App\Models\MailboxMessage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MailboxMessageService
{
    public function __construct(private readonly MessageSanitizer $sanitizer) {}

    public function list(Mailbox $mailbox, int $perPage = 20): LengthAwarePaginator
    {
        return $mailbox->messages()->whereNull('deleted_at')->latest('received_at')->paginate($perPage);
    }

    /** @param array<string, mixed> $data */
    public function store(Mailbox $mailbox, array $data): MailboxMessage
    {
        return DB::transaction(function () use ($mailbox, $data): MailboxMessage {
            $plain = trim((string) ($data['plain_text_body'] ?? ''));
            $preview = trim((string) ($data['preview_text'] ?? Str::limit($plain, 180, '')));
            $message = $mailbox->messages()->create([
                ...$data,
                'preview_text' => Str::limit(strip_tags($preview), 240, ''),
                'sanitized_html_body' => $this->sanitizer->sanitize($data['html_body'] ?? $data['sanitized_html_body'] ?? null),
                'raw_headers' => $this->safeHeaders($data['raw_headers'] ?? []),
            ]);

            $mailbox->forceFill([
                'message_count' => $mailbox->messages()->whereNull('deleted_at')->count(),
                'last_activity_at' => $message->received_at,
            ])->save();

            return $message;
        });
    }

    public function belongsTo(Mailbox $mailbox, MailboxMessage $message): bool
    {
        return $message->mailbox_id === $mailbox->id && $message->deleted_at === null;
    }

    /** @param mixed $headers @return array<string, string> */
    private function safeHeaders(mixed $headers): array
    {
        if (! is_array($headers)) {
            return [];
        }

        return collect($headers)->mapWithKeys(fn (mixed $value, mixed $key): array => [
            Str::limit(strip_tags((string) $key), 100, '') => Str::limit(preg_replace('/[\r\n]+/', ' ', strip_tags((string) $value)) ?? '', 2000, ''),
        ])->all();
    }
}
