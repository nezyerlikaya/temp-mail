<?php

namespace App\Services\Sections;

use App\Models\Locale;
use App\Models\Section;
use Illuminate\Support\Collection;

class SectionCollectionResolver
{
    /**
     * @return Collection<int, Section>
     */
    public function resolve(Locale|int $locale, string $placement, string $device = 'all'): Collection
    {
        $localeId = $locale instanceof Locale ? $locale->id : $locale;

        return Section::query()
            ->with(['locale', 'items'])
            ->where('locale_id', $localeId)
            ->where('placement', $placement)
            ->where('status', 'active')
            ->where(function ($query) use ($device): void {
                $query->where('device_visibility', 'all');

                if (in_array($device, ['desktop', 'mobile'], true)) {
                    $query->orWhere('device_visibility', $device);
                }
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }
}
