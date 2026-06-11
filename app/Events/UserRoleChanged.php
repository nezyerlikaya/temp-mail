<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserRoleChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly User $actor,
        public readonly User $subject,
        public readonly string $oldRole,
        public readonly string $newRole,
    ) {}
}
