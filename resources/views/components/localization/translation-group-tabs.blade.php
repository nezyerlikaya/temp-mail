@props(['groups', 'filters', 'total'])

@php
    $activeGroup = $filters['group'] ?? 'all';
@endphp

<nav {{ $attributes->merge(['class' => 'overflow-x-auto rounded-lg border border-stone-200 bg-white p-2 shadow-sm']) }} aria-label="Translation groups">
    <div class="flex min-w-max gap-2">
        <a
            href="{{ route('admin.translation-center.index', array_filter([...$filters, 'group' => 'all'])) }}"
            @class([
                'rounded-md px-3 py-2 text-sm font-extrabold transition focus:outline-none focus:ring-4 focus:ring-teal-600/20',
                'bg-stone-950 text-white' => $activeGroup === 'all',
                'text-stone-700 hover:bg-stone-50' => $activeGroup !== 'all',
            ])
            @if ($activeGroup === 'all') aria-current="page" @endif
        >
            All keys
            <span class="ml-1 text-xs opacity-75">{{ $total }}</span>
        </a>

        @foreach ($groups as $key => $label)
            <a
                href="{{ route('admin.translation-center.index', array_filter([...$filters, 'group' => $key])) }}"
                @class([
                    'rounded-md px-3 py-2 text-sm font-extrabold transition focus:outline-none focus:ring-4 focus:ring-teal-600/20',
                    'bg-stone-950 text-white' => $activeGroup === $key,
                    'text-stone-700 hover:bg-stone-50' => $activeGroup !== $key,
                ])
                @if ($activeGroup === $key) aria-current="page" @endif
            >
                {{ $label }}
            </a>
        @endforeach
    </div>
</nav>
