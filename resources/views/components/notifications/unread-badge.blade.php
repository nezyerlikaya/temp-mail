@props(['unread' => false])

@if ($unread)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full bg-teal-100 px-2 py-1 text-xs font-extrabold text-teal-900']) }}>
        Unread
    </span>
@else
    <span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full bg-stone-100 px-2 py-1 text-xs font-bold text-stone-600']) }}>
        Read
    </span>
@endif
