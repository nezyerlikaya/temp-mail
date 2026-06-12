@props(['filters' => [], 'summary' => []])

<div class="flex flex-wrap items-center gap-2 rounded-lg border border-stone-200 bg-white p-3 shadow-sm" aria-label="Trash filter">
    <a href="{{ route('admin.sections-studio.index', [...$filters, 'status' => 'all']) }}" class="inline-flex min-h-9 items-center rounded-lg px-3 text-xs font-extrabold {{ ($filters['status'] ?? 'all') !== 'trashed' ? 'bg-stone-950 text-white' : 'border border-stone-300 text-stone-700' }} focus:outline-none focus:ring-4 focus:ring-teal-600/20">
        Active workspace
    </a>
    <a href="{{ route('admin.sections-studio.index', [...$filters, 'status' => 'trashed']) }}" class="inline-flex min-h-9 items-center rounded-lg px-3 text-xs font-extrabold {{ ($filters['status'] ?? 'all') === 'trashed' ? 'bg-red-700 text-white' : 'border border-red-200 bg-red-50 text-red-800' }} focus:outline-none focus:ring-4 focus:ring-red-700/20">
        Trash {{ $summary['trashed'] ?? 0 }}
    </a>
</div>
