@props(['active' => 'categories'])

@php
    $tabs = [
        'categories' => ['label' => 'Categories', 'href' => route('admin.taxonomy.index', ['tab' => 'categories'])],
        'tags' => ['label' => 'Tags', 'href' => route('admin.taxonomy.index', ['tab' => 'tags'])],
    ];
@endphp

<nav {{ $attributes->merge(['class' => 'flex flex-wrap gap-2 rounded-lg border border-stone-200 bg-white p-1 shadow-sm']) }} aria-label="Taxonomy sections">
    @foreach ($tabs as $key => $tab)
        <a
            href="{{ $tab['href'] }}"
            @class([
                'inline-flex min-h-10 items-center justify-center rounded-md px-4 text-sm font-extrabold transition focus:outline-none focus:ring-4 focus:ring-teal-600/20',
                'bg-stone-950 text-white shadow-sm' => $active === $key,
                'text-stone-600 hover:bg-stone-50 hover:text-stone-950' => $active !== $key,
            ])
            @if ($active === $key) aria-current="page" @endif
        >
            {{ $tab['label'] }}
        </a>
    @endforeach
</nav>
