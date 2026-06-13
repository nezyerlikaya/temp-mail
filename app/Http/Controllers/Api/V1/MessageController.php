<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Mailbox;
use App\Models\MailboxMessage;
use App\Services\Api\ApiJsonResponse;
use App\Services\Api\MailboxApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(private readonly MailboxApiService $mailboxes, private readonly ApiJsonResponse $json) {}

    public function index(Request $request, Mailbox $mailbox): JsonResponse
    {
        $paginator = $this->mailboxes->messages($request->attributes->get('api_key'), $mailbox, (int) $request->integer('per_page', 15));

        if (! $paginator) {
            return $this->json->error('not_found', 'Mailbox was not found for this API key.', 404);
        }

        return $this->json->success(
            $paginator->getCollection()->map(fn (MailboxMessage $message): array => $this->messageSummary($message))->all(),
            ['pagination' => $this->json->pagination($paginator)],
        );
    }

    public function show(Request $request, Mailbox $mailbox, MailboxMessage $message): JsonResponse
    {
        $message = $this->mailboxes->message($request->attributes->get('api_key'), $mailbox, $message);

        if (! $message) {
            return $this->json->error('not_found', 'Message was not found for this API key.', 404);
        }

        return $this->json->success([
            ...$this->messageSummary($message),
            'plain_text_body' => $message->plain_text_body,
            'html_body' => $message->sanitized_html_body,
            'headers' => $message->raw_headers ?? [],
        ]);
    }

    /** @return array<string, mixed> */
    private function messageSummary(MailboxMessage $message): array
    {
        return [
            'id' => $message->id,
            'sender_email' => $message->sender_email,
            'sender_name' => $message->sender_name,
            'subject' => $message->subject,
            'preview_text' => $message->preview_text,
            'attachment_count' => $message->attachment_count,
            'message_size' => $message->message_size,
            'received_at' => $message->received_at?->toIso8601String(),
            'read_at' => $message->read_at?->toIso8601String(),
        ];
    }
}
