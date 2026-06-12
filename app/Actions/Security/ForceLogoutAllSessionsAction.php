<?php

namespace App\Actions\Security;

use App\Models\User;
use App\Services\Security\AdminSessionService;

class ForceLogoutAllSessionsAction
{
    public function __construct(private readonly AdminSessionService $sessions) {}

    public function handle(User $actor, ?string $currentSessionId): int
    {
        return $this->sessions->forceLogoutAll($actor, $currentSessionId);
    }
}
