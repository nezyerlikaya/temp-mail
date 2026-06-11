<?php

namespace App\Services\Users;

class UserStatusService
{
    /**
     * @return array<string, string>
     */
    public function statuses(): array
    {
        return [
            'active' => 'Active',
            'suspended' => 'Suspended',
            'invited' => 'Invited',
        ];
    }

    public function isValid(string $status): bool
    {
        return array_key_exists($status, $this->statuses());
    }
}
