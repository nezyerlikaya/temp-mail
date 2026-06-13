<?php

namespace App\Services\Abuse;

use App\Models\AbuseReport;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;

class AbuseResolutionService
{
    public function __construct(
        private readonly AbuseCaseTimelineService $timeline,
        private readonly AbuseReporterNotificationService $reporterNotification,
        private readonly AuditLogger $audit,
    ) {}

    /** @param array<string, mixed> $payload */
    public function resolve(User $actor, AbuseReport $report, array $payload): AbuseReport
    {
        return DB::transaction(function () use ($actor, $report, $payload): AbuseReport {
            $report->forceFill([
                'status' => 'resolved',
                'resolution_outcome' => $payload['resolution_outcome'],
                'resolution_reason' => trim(strip_tags($payload['resolution_reason'])),
                'resolution_summary' => filled($payload['resolution_summary'] ?? null) ? trim(strip_tags($payload['resolution_summary'])) : null,
                'resolved_by' => $actor->id,
                'resolved_at' => now(),
                'archived_at' => null,
                'retention_review_at' => now()->addYear(),
            ])->save();

            $this->reporterNotification->prepare($report, $payload['reporter_response_subject'] ?? null, $payload['reporter_response_body'] ?? null);
            $this->timeline->record($report, $actor, 'case_resolved', 'Case resolved as '.str($report->resolution_outcome)->replace('_', ' ')->toString().'.', ['outcome' => $report->resolution_outcome]);
            $this->audit->record('abuse.case_resolved', $actor, null, [
                'case_reference' => $report->case_reference,
                'outcome' => $report->resolution_outcome,
                'reporter_response_prepared' => filled($report->reporter_response_body),
            ], ['module' => 'trust', 'target' => $report]);

            return $report->refresh();
        });
    }

    /** @param array<string, mixed> $payload */
    public function reject(User $actor, AbuseReport $report, array $payload): AbuseReport
    {
        return DB::transaction(function () use ($actor, $report, $payload): AbuseReport {
            $report->forceFill([
                'status' => 'rejected',
                'resolution_outcome' => 'rejected_as_invalid',
                'resolution_reason' => trim(strip_tags($payload['resolution_reason'])),
                'resolution_summary' => filled($payload['resolution_summary'] ?? null) ? trim(strip_tags($payload['resolution_summary'])) : null,
                'resolved_by' => $actor->id,
                'resolved_at' => now(),
                'retention_review_at' => now()->addYear(),
            ])->save();

            $this->reporterNotification->prepare($report, $payload['reporter_response_subject'] ?? null, $payload['reporter_response_body'] ?? null);
            $this->timeline->record($report, $actor, 'case_rejected', 'Case rejected as invalid.');
            $this->audit->record('abuse.case_rejected', $actor, null, ['case_reference' => $report->case_reference], ['module' => 'trust', 'target' => $report]);

            return $report->refresh();
        });
    }

    public function reopen(User $actor, AbuseReport $report, string $reason): AbuseReport
    {
        $report->forceFill([
            'status' => 'reviewing',
            'resolution_outcome' => null,
            'resolution_reason' => null,
            'resolution_summary' => null,
            'resolved_by' => null,
            'resolved_at' => null,
            'reopened_at' => now(),
            'archived_at' => null,
            'retention_review_at' => null,
        ])->save();

        $this->timeline->record($report, $actor, 'case_reopened', 'Case reopened for further review.', ['reason_excerpt' => str(strip_tags($reason))->limit(80)->toString()]);
        $this->audit->record('abuse.case_reopened', $actor, null, ['case_reference' => $report->case_reference], ['module' => 'trust', 'target' => $report]);

        return $report->refresh();
    }

    public function archive(User $actor, AbuseReport $report, string $reason): AbuseReport
    {
        $report->forceFill(['status' => 'archived', 'archived_at' => now(), 'retention_review_at' => now()->addYear()])->save();
        $this->timeline->record($report, $actor, 'case_archived', 'Closed case archived.', ['reason_excerpt' => str(strip_tags($reason))->limit(80)->toString()]);
        $this->audit->record('abuse.case_archived', $actor, null, ['case_reference' => $report->case_reference], ['module' => 'trust', 'target' => $report]);

        return $report->refresh();
    }
}
