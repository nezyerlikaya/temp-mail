<?php

namespace App\Services\Audit;

use App\Models\UserAuditEvent;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AuditTamperCheckService
{
    /** @return array{status: string, title: string, message: string} */
    public function readiness(): array
    {
        try {
            if (! Schema::hasTable('user_audit_events')) {
                return [
                    'status' => 'warning',
                    'title' => 'Audit storage unavailable',
                    'message' => 'Audit storage has not been migrated yet.',
                ];
            }

            $latest = UserAuditEvent::query()->latest('id')->value('id');

            return [
                'status' => 'ready',
                'title' => 'Tamper warning readiness',
                'message' => $latest
                    ? 'Sequential audit records are available for future integrity checks. External immutable storage is not enabled in MVP.'
                    : 'Audit storage is ready. Integrity warnings will become meaningful after records exist.',
            ];
        } catch (Throwable) {
            return [
                'status' => 'warning',
                'title' => 'Tamper warning readiness unavailable',
                'message' => 'Audit integrity readiness could not be checked safely.',
            ];
        }
    }
}
