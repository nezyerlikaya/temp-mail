@props(['paginator'])

@if ($paginator->hasPages())
    <nav {{ $attributes->merge(['class' => 'flex flex-col gap-3 rounded-lg border border-stone-200 bg-white p-4 text-sm shadow-sm sm:flex-row sm:items-center sm:justify-between']) }} aria-label="Pagination">
        <p class="font-bold text-stone-600">
            Showing {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} of {{ $paginator->total() }}
        </p>
        <div class="flex gap-2">
            @if ($paginator->onFirstPage())
                <span class="rounded-lg border border-stone-200 px-3 py-2 font-bold text-stone-400">Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="rounded-lg border border-stone-300 px-3 py-2 font-bold text-stone-700 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-600/20">Previous</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="rounded-lg border border-stone-300 px-3 py-2 font-bold text-stone-700 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-600/20">Next</a>
            @else
                <span class="rounded-lg border border-stone-200 px-3 py-2 font-bold text-stone-400">Next</span>
            @endif
        </div>
    </nav>
@endif
