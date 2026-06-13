<?php

namespace App\Actions\Abuse;

use App\Models\AbuseCaseNote;
use App\Models\AbuseReport;
use App\Models\User;
use App\Services\Abuse\AbuseCaseTimelineService;
use App\Services\Audit\AuditLogger;

class AddAbuseCaseNoteAction
{
    public function __construct(private readonly AbuseCaseTimelineService $timeline, private readonly AuditLogger $audit) {}

    public function handle(User $actor, AbuseReport $report, string $body): AbuseCaseNote
    {
        $note = $report->notes()->create(['author_id' => $actor->id, 'body' => trim(strip_tags($body))]);
        $this->timeline->record($report, $actor, 'note_added', 'Internal investigation note added.', ['note_id' => $note->id]);
        $this->audit->record('abuse.note_added', $actor, null, ['case_reference' => $report->case_reference, 'note_id' => $note->id], ['module' => 'trust', 'target' => $report]);

        return $note;
    }
}
