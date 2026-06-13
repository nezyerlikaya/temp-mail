<?php

namespace App\Actions\Api;

use App\Models\ApiKey;
use App\Models\User;
use App\Services\Api\ApiKeyService;
use App\Services\Audit\AuditLogger;

class RegenerateApiKeyAction
{
    public function __construct(private readonly ApiKeyService $keys, private readonly AuditLogger $audit) {}

    /** @return array{key: ApiKey, secret: string} */
    public function handle(User $actor, ApiKey $key): array
    {
        $oldPrefix = $key->key_prefix;
        $result = $this->keys->regenerate($key);

        $this->audit->record('api_key.regenerated', $actor, $result['key']->user, [
            'key_id' => $result['key']->id,
            'old_key_prefix' => $oldPrefix,
            'key_prefix' => $result['key']->key_prefix,
            'environment' => $result['key']->environment,
        ], ['module' => 'api-access', 'target' => $result['key']]);

        return $result;
    }
}
