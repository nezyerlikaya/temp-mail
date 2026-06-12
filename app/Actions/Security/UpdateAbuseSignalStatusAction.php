<?php

namespace App\Actions\Security;

use App\Models\AbuseSignal;
use App\Models\User;
use App\Services\Audit\AuditLogger;

class UpdateAbuseSignalStatusAction
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function handle(User $actor, AbuseSignal $signal, string $status): AbuseSignal
    {
        $previous = $signal->status;

        $signal->forceFill([
            'status' => $status,
            'reviewed_by' => $actor->id,
            'resolved_at' => in_array($status, ['resolved', 'ignored'], true) ? now() : null,
        ])->save();

        $this->audit->record('security.abuse_signal_status_changed', $actor, null, [
            'signal_id' => $signal->id,
            'signal_type' => $signal->signal_type,
            'from' => $previous,
            'to' => $status,
        ], [
            'module' => 'security',
            'action' => 'Abuse signal status changed',
            'severity' => $signal->severity === 'critical' ? 'critical' : 'warning',
            'target' => $signal,
        ]);

        return $signal->refresh();
    }
}
