@props(['label', 'items'])

<section aria-labelledby="sidebar-group-{{ str($label)->slug() }}">
    <h2 id="sidebar-group-{{ str($label)->slug() }}" class="px-3 text-xs font-bold uppercase text-stone-500">{{ $label }}</h2>
    <ul class="mt-1.5 space-y-0.5">
        @foreach ($items as $item)
            <li>
                <x-admin.sidebar-link :item="$item" />
            </li>
        @endforeach
    </ul>
</section>
