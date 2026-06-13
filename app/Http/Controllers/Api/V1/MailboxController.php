<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateMailboxApiRequest;
use App\Models\Mailbox;
use App\Services\Api\ApiJsonResponse;
use App\Services\Api\MailboxApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MailboxController extends Controller
{
    public function __construct(private readonly MailboxApiService $mailboxes, private readonly ApiJsonResponse $json) {}

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->mailboxes->list($request->attributes->get('api_key'), (int) $request->integer('per_page', 15));

        return $this->json->success(
            $paginator->getCollection()->map(fn (Mailbox $mailbox): array => $this->mailboxPayload($mailbox))->all(),
            ['pagination' => $this->json->pagination($paginator)],
        );
    }

    public function store(CreateMailboxApiRequest $request): JsonResponse
    {
        $mailbox = $this->mailboxes->create($request->attributes->get('api_key'), $request->validated());

        return $this->json->success($this->mailboxPayload($mailbox), ['environment' => $mailbox->api_environment], 201);
    }

    public function show(Request $request, Mailbox $mailbox): JsonResponse
    {
        $owned = $this->mailboxes->owned($request->attributes->get('api_key'), $mailbox);

        if (! $owned) {
            return $this->json->error('not_found', 'Mailbox was not found for this API key.', 404);
        }

        return $this->json->success($this->mailboxPayload($owned));
    }

    public function destroy(Request $request, Mailbox $mailbox): JsonResponse
    {
        $expired = $this->mailboxes->expire($request->attributes->get('api_key'), $mailbox);

        if (! $expired) {
            return $this->json->error('not_found', 'Mailbox was not found for this API key.', 404);
        }

        return $this->json->success($this->mailboxPayload($expired), ['action' => 'expired']);
    }

    /** @return array<string, mixed> */
    private function mailboxPayload(Mailbox $mailbox): array
    {
        return [
            'id' => $mailbox->id,
            'address' => $mailbox->address,
            'domain' => $mailbox->domain?->domain_name,
            'status' => $mailbox->status,
            'environment' => $mailbox->api_environment,
            'message_count' => $mailbox->message_count,
            'expires_at' => $mailbox->expires_at?->toIso8601String(),
            'created_at' => $mailbox->created_at?->toIso8601String(),
        ];
    }
}
