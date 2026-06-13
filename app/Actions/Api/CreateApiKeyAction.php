<?php

namespace App\Actions\Api;

use App\Models\User;
use App\Services\Api\ApiKeyService;
use App\Services\Audit\AuditLogger;

class CreateApiKeyAction
{
    public function __construct(private readonly ApiKeyService $keys, private readonly AuditLogger $audit) {}

    /** @param array<string, mixed> $data */
    public function handle(User $actor, User $owner, array $data): array
    {
        $result = $this->keys->create($actor, $owner, $data);

        $this->audit->record('api_key.created', $actor, $owner, [
            'key_id' => $result['key']->id,
            'key_prefix' => $result['key']->key_prefix,
            'environment' => $result['key']->environment,
            'scopes' => $result['key']->scopes,
        ], ['module' => 'api-access', 'target' => $result['key']]);

        return $result;
    }
}
