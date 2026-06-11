<?php

namespace App\Services\Users;

use App\Models\User;
use App\Services\Audit\AuditLogger;

class UserAuditLogger
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** @param array<string, mixed> $metadata */
    public function record(User $actor, ?User $subject, string $event, array $metadata = []): void
    {
        $this->audit->record($event, $actor, $subject, $metadata);
    }
}
