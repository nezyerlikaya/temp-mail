@props(['section', 'types' => [], 'placements' => []])

<article class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="truncate text-sm font-extrabold text-stone-950">{{ $section->title }}</p>
            <p class="mt-1 text-xs font-bold text-stone-500">{{ $placements[$section->placement] ?? $section->placement }}</p>
        </div>
        <x-sections.status-badge :status="$section->status" />
    </div>

    <p class="mt-3 line-clamp-2 min-h-10 text-sm leading-5 text-stone-600">{{ $section->subtitle ?: 'Description readiness pending.' }}</p>

    <div class="mt-4 flex flex-wrap items-center gap-2">
        <x-sections.language-badge :locale="$section->locale" />
        <x-sections.type-badge :type="$section->section_type" :types="$types" />
        <span class="inline-flex rounded-full border border-stone-200 bg-stone-50 px-2.5 py-1 text-xs font-extrabold text-stone-700">
            {{ $section->items_count }} items
        </span>
    </div>

    <div class="mt-4 flex items-center justify-between text-xs text-stone-500">
        <span>{{ $section->creator?->name ?? 'System' }}</span>
        <span>Order {{ $section->sort_order }}</span>
    </div>

    <a href="{{ route('admin.sections-studio.edit', $section) }}" class="mt-4 inline-flex min-h-10 w-full items-center justify-center rounded-lg bg-stone-950 px-3 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-teal-600/20">
        Edit section
    </a>
</article>
