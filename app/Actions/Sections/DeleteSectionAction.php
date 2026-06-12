<?php

namespace App\Actions\Sections;

use App\Models\Section;
use App\Models\User;
use App\Services\Sections\SectionAuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteSectionAction
{
    public function __construct(private readonly SectionAuditLogger $audit) {}

    public function handle(User $actor, Section $section): void
    {
        if ($section->status !== 'trashed') {
            throw ValidationException::withMessages([
                'confirm_delete' => 'Move this section to trash before permanent deletion.',
            ]);
        }

        DB::transaction(function () use ($actor, $section): void {
            $metadata = [
                'section_id' => $section->id,
                'locale_id' => $section->locale_id,
                'section_type' => $section->section_type,
                'placement' => $section->placement,
                'status' => $section->status,
            ];

            $section->items()->delete();
            $section->delete();

            $this->audit->recordDeleted($actor, $metadata);
        });
    }
}
