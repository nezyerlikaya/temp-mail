<?php

namespace App\Services\Api;

class ApiDocumentationService
{
    /** @return array<string, mixed> */
    public function payload(): array
    {
        return [
            'base_path' => '/api/v1',
            'authentication' => 'Send Authorization: Bearer <YOUR_API_KEY>. Secrets are shown once and never stored in plaintext.',
            'rate_limits' => 'Monthly request limits come from the key owner plan. Every response includes the same JSON envelope, and usage can be checked with the usage endpoint.',
            'environments' => [
                'test' => 'tm_test_ keys create and read test-isolated API mailboxes.',
                'live' => 'tm_live_ keys access permitted live API resources.',
            ],
            'response_format' => ['data' => '{} or []', 'meta' => '{}', 'error' => null],
            'error_format' => ['data' => null, 'meta' => '{}', 'error' => ['code' => 'error_code', 'message' => 'Human-readable message']],
            'endpoints' => [
                ['method' => 'POST', 'path' => '/api/v1/mailboxes', 'scope' => 'mailbox:create', 'description' => 'Create a mailbox on an active public receiving domain.'],
                ['method' => 'GET', 'path' => '/api/v1/mailboxes', 'scope' => 'mailbox:read', 'description' => 'List owned mailboxes.'],
                ['method' => 'GET', 'path' => '/api/v1/mailboxes/{id}', 'scope' => 'mailbox:read', 'description' => 'Get one owned mailbox.'],
                ['method' => 'DELETE', 'path' => '/api/v1/mailboxes/{id}', 'scope' => 'mailbox:delete', 'description' => 'Expire an owned mailbox.'],
                ['method' => 'GET', 'path' => '/api/v1/mailboxes/{id}/messages', 'scope' => 'message:read', 'description' => 'List message metadata for an owned mailbox.'],
                ['method' => 'GET', 'path' => '/api/v1/mailboxes/{id}/messages/{message_id}', 'scope' => 'message:read', 'description' => 'Read one message for an owned mailbox.'],
                ['method' => 'GET', 'path' => '/api/v1/domains', 'scope' => 'domain:read', 'description' => 'List active public domains.'],
                ['method' => 'GET', 'path' => '/api/v1/usage', 'scope' => 'usage:read', 'description' => 'Read current usage and limits.'],
            ],
            'examples' => [
                'create_mailbox' => "curl -X POST /api/v1/mailboxes -H 'Authorization: Bearer <YOUR_API_KEY>' -H 'Accept: application/json' -d 'domain=example.test&local_part=demo'",
                'list_messages' => "curl /api/v1/mailboxes/{id}/messages -H 'Authorization: Bearer <YOUR_API_KEY>' -H 'Accept: application/json'",
            ],
        ];
    }
}
