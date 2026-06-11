<?php

namespace App\Services\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserAuditLogger
{
    /** @param array<string, mixed> $metadata */
    public function record(User $actor, User $subject, string $event, array $metadata = []): void
    {
        DB::table('user_audit_events')->insert([
            'actor_id' => $actor->id,
            'subject_id' => $subject->id,
            'event' => $event,
            'metadata' => json_encode($metadata, JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
