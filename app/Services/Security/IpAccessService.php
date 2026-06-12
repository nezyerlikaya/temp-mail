<?php

namespace App\Services\Security;

class IpAccessService
{
    public function __construct(private readonly RateLimitPolicyStore $store) {}

    /** @return array<string, mixed> */
    public function readiness(): array
    {
        $settings = $this->store->ipAccess();
        $allowlistCount = count($settings['allowlist']);
        $blocklistCount = count($settings['blocklist']);

        return [
            'status' => $allowlistCount > 0 || $blocklistCount > 0 || $settings['temporary_block_ready'] ? 'ready' : 'passive',
            'message' => $allowlistCount > 0 || $blocklistCount > 0
                ? "{$allowlistCount} allowed IPs and {$blocklistCount} blocked IPs configured."
                : 'No IP rules configured yet. Core admin gates still protect access.',
        ];
    }
}
