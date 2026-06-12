<?php

namespace App\Services\Security;

use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AdminSessionService
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function forceLogoutAll(User $actor, ?string $currentSessionId): int
    {
        $deleted = 0;

        try {
            if (Schema::hasTable((string) config('session.table', 'sessions'))) {
                $query = DB::table((string) config('session.table', 'sessions'))->whereNotNull('user_id');

                if ($currentSessionId) {
                    $query->where('id', '!=', $currentSessionId);
                }

                $deleted = $query->delete();
            }
        } catch (Throwable) {
            $deleted = 0;
        }

        $this->audit->record('security.sessions_force_logged_out', $actor, $actor, [
            'sessions_removed' => $deleted,
        ], [
            'module' => 'security',
            'action' => 'Force logout sessions',
            'severity' => 'critical',
        ]);

        return $deleted;
    }
}
