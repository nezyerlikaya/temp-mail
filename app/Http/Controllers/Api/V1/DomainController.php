<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Services\Api\ApiJsonResponse;
use App\Services\Api\MailboxApiService;
use Illuminate\Http\JsonResponse;

class DomainController extends Controller
{
    public function __construct(private readonly MailboxApiService $mailboxes, private readonly ApiJsonResponse $json) {}

    public function index(): JsonResponse
    {
        return $this->json->success($this->mailboxes->domains()->map(fn (Domain $domain): array => [
            'id' => $domain->id,
            'domain' => $domain->domain_name,
            'display_name' => $domain->display_name,
            'is_default' => $domain->is_default,
        ])->all());
    }
}
