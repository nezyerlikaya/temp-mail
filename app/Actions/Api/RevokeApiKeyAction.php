<?php

namespace App\Actions\Api;

use App\Models\ApiKey;
use App\Models\User;
use App\Services\Api\ApiKeyService;
use App\Services\Audit\AuditLogger;

class RevokeApiKeyAction
{
    public function __construct(private readonly ApiKeyService $keys, private readonly AuditLogger $audit) {}

    public function handle(User $actor, ApiKey $key): ApiKey
    {
        $revoked = $this->keys->revoke($key);

        $this->audit->record('api_key.revoked', $actor, $revoked->user, [
            'key_id' => $revoked->id,
            'key_prefix' => $revoked->key_prefix,
            'environment' => $revoked->environment,
        ], ['module' => 'api-access', 'target' => $revoked]);

        return $revoked;
    }
}
