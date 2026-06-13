<?php

namespace App\Services\Api;

class ApiScopeRegistry
{
    /** @return array<string, string> */
    public function all(): array
    {
        return [
            'mailbox:create' => 'Create temporary mailboxes',
            'mailbox:read' => 'Read mailbox metadata',
            'mailbox:delete' => 'Delete temporary mailboxes',
            'message:read' => 'Read mailbox messages',
            'domain:read' => 'Read available receiving domains',
            'usage:read' => 'Read API usage summaries',
        ];
    }

    /** @param array<int, string> $scopes */
    public function clean(array $scopes): array
    {
        return collect($scopes)
            ->map(fn (string $scope): string => trim($scope))
            ->filter(fn (string $scope): bool => array_key_exists($scope, $this->all()))
            ->unique()
            ->values()
            ->all();
    }
}
