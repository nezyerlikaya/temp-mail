<?php

namespace App\Services\Abuse;

use App\Models\AbuseReport;
use App\Models\User;
use App\Services\Audit\AuditLogger;

class AbuseCaseService
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function assign(User $actor, AbuseReport $report, ?User $assignee): AbuseReport
    {
        $previous = $report->assigned_to;
        $report->forceFill(['assigned_to' => $assignee?->id])->save();

        $this->audit->record('abuse.case_assigned', $actor, null, [
            'case_reference' => $report->case_reference,
            'previous_assignee_id' => $previous,
            'assignee_id' => $assignee?->id,
        ], ['module' => 'trust', 'target' => $report]);

        return $report->refresh();
    }

    public function updateStatus(User $actor, AbuseReport $report, string $status): AbuseReport
    {
        $previous = $report->status;
        $report->forceFill(['status' => $status])->save();

        $this->audit->record('abuse.case_status_changed', $actor, null, [
            'case_reference' => $report->case_reference,
            'previous_status' => $previous,
            'status' => $status,
            'priority' => $report->priority,
        ], ['module' => 'trust', 'target' => $report]);

        return $report->refresh();
    }

    /** @return array<string, int> */
    public function summary(): array
    {
        return [
            'new' => AbuseReport::query()->where('status', 'new')->count(),
            'reviewing' => AbuseReport::query()->where('status', 'reviewing')->count(),
            'awaiting_information' => AbuseReport::query()->where('status', 'awaiting_information')->count(),
            'critical' => AbuseReport::query()->where('priority', 'critical')->whereNotIn('status', ['resolved', 'rejected', 'archived'])->count(),
            'unassigned' => AbuseReport::query()->whereNull('assigned_to')->whereNotIn('status', ['resolved', 'rejected', 'archived'])->count(),
        ];
    }
}
