@props(['notification'])

@if ((int) $notification->occurrence_count > 1)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full bg-indigo-50 px-2 py-1 text-xs font-extrabold text-indigo-800 ring-1 ring-inset ring-indigo-200']) }}>
        {{ $notification->occurrence_count }} occurrences
    </span>
@endif
