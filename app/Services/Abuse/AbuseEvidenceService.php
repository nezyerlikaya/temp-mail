<?php

namespace App\Services\Abuse;

use App\Models\AbuseEvidence;
use App\Models\AbuseReport;
use App\Models\MediaAsset;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Media\MediaValidationService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AbuseEvidenceService
{
    public function __construct(
        private readonly MediaValidationService $validation,
        private readonly AbuseCaseTimelineService $timeline,
        private readonly AuditLogger $audit,
    ) {}

    public function add(User $actor, AbuseReport $report, MediaAsset $asset, ?string $label): AbuseEvidence
    {
        if (! in_array($asset->mime_type, $this->validation->allowedMimeTypes(), true) || $asset->size_bytes > $this->validation->maxKilobytes() * 1024) {
            throw ValidationException::withMessages(['media_asset_id' => 'Evidence must be a supported Media Library file no larger than 10 MB.']);
        }

        if ($asset->status === 'trashed') {
            throw ValidationException::withMessages(['media_asset_id' => 'Restore this Media Library item before using it as evidence.']);
        }

        $extension = pathinfo($asset->file_name, PATHINFO_EXTENSION);
        $privatePath = 'abuse-evidence/'.$report->case_reference.'/'.$asset->uuid.($extension !== '' ? '.'.$extension : '');
        $contents = Storage::disk($asset->disk)->get($asset->path);

        if (! Storage::disk('local')->put($privatePath, $contents)) {
            throw ValidationException::withMessages(['media_asset_id' => 'The protected evidence copy could not be created.']);
        }

        $evidence = $report->evidences()->firstOrCreate(
            ['media_asset_id' => $asset->id],
            ['label' => $label, 'is_sensitive' => true, 'private_disk' => 'local', 'private_path' => $privatePath, 'added_by' => $actor->id],
        );

        $this->timeline->record($report, $actor, 'evidence_added', 'Protected evidence reference added.', ['evidence_id' => $evidence->id, 'media_uuid' => $asset->uuid]);
        $this->audit->record('abuse.evidence_added', $actor, null, [
            'case_reference' => $report->case_reference,
            'evidence_id' => $evidence->id,
            'media_uuid' => $asset->uuid,
            'mime_type' => $asset->mime_type,
            'size_bytes' => $asset->size_bytes,
        ], ['module' => 'trust', 'target' => $report]);

        return $evidence->load('mediaAsset', 'addedBy');
    }

    public function remove(User $actor, AbuseReport $report, AbuseEvidence $evidence): void
    {
        abort_unless($evidence->abuse_report_id === $report->id, 404);
        Storage::disk($evidence->private_disk)->delete($evidence->private_path);
        $evidenceId = $evidence->id;
        $evidence->delete();

        $this->timeline->record($report, $actor, 'evidence_removed', 'Protected evidence reference removed.', ['evidence_id' => $evidenceId]);
        $this->audit->record('abuse.evidence_removed', $actor, null, ['case_reference' => $report->case_reference, 'evidence_id' => $evidenceId], ['module' => 'trust', 'target' => $report]);
    }
}
