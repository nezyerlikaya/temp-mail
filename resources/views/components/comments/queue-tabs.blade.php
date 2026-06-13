@props(['filters', 'summary'])

@php
    $tabs = [
        'pending' => ['label' => 'Pending', 'count' => $summary['pending']],
        'approved' => ['label' => 'Approved', 'count' => $summary['approved']],
        'spam' => ['label' => 'Spam', 'count' => $summary['spam']],
        'trashed' => ['label' => 'Trash', 'count' => $summary['trashed']],
        'all' => ['label' => 'All', 'count' => array_sum([$summary['pending'], $summary['approved'], $summary['spam'], $summary['trashed']])],
    ];
@endphp

<nav {{ $attributes->merge(['class' => 'overflow-x-auto rounded-lg border border-stone-200 bg-white p-2 shadow-sm']) }} aria-label="Comment queue status">
    <div class="flex min-w-max gap-2">
        @foreach ($tabs as $status => $tab)
            <a href="{{ route('admin.comment-moderation.index', array_filter([...$filters, 'status' => $status])) }}"
                @class([
                    'rounded-md px-3 py-2 text-sm font-extrabold transition focus:outline-none focus:ring-4 focus:ring-teal-600/20',
                    'bg-stone-950 text-white' => ($filters['status'] ?? 'pending') === $status,
                    'text-stone-700 hover:bg-stone-50' => ($filters['status'] ?? 'pending') !== $status,
                ])
                @if (($filters['status'] ?? 'pending') === $status) aria-current="page" @endif
            >
                {{ $tab['label'] }}
                <span class="ml-1 text-xs opacity-75">{{ $tab['count'] }}</span>
            </a>
        @endforeach
    </div>
</nav>
