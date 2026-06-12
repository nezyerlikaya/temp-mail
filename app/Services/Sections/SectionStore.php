<?php

namespace App\Services\Sections;

use App\Models\Locale;
use App\Models\Section;
use Illuminate\Support\Collection;

class SectionStore
{
    public function __construct(
        private readonly SectionTypeRegistry $types,
        private readonly SectionPlacementRegistry $placements,
    ) {}

    /** @return array<string, string> */
    public function statuses(): array
    {
        return [
            'draft' => 'Draft',
            'active' => 'Active',
            'hidden' => 'Hidden',
            'trashed' => 'Trashed',
        ];
    }

    /** @return array<string, string> */
    public function editorStatuses(): array
    {
        return [
            'draft' => 'Draft',
            'active' => 'Active',
            'hidden' => 'Hidden',
        ];
    }

    /** @return array<string, string> */
    public function visibilities(): array
    {
        return [
            'public' => 'Public',
            'guests' => 'Guests',
            'members' => 'Members',
        ];
    }

    /** @return array<string, int> */
    public function summary(): array
    {
        return [
            'total' => Section::query()->count(),
            'draft' => Section::query()->where('status', 'draft')->count(),
            'active' => Section::query()->where('status', 'active')->count(),
            'hidden' => Section::query()->where('status', 'hidden')->count(),
            'trashed' => Section::query()->where('status', 'trashed')->count(),
        ];
    }

    /** @return Collection<int, Locale> */
    public function locales(): Collection
    {
        return Locale::query()->orderByDesc('is_default')->orderBy('sort_order')->orderBy('language_name')->get();
    }

    /** @return array<string, string> */
    public function types(): array
    {
        return $this->types->options();
    }

    /** @return array<string, string> */
    public function placements(): array
    {
        return $this->placements->options();
    }
}
