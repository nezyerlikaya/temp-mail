@props(['status'])

@php($classes = [
    'pass' => 'bg-teal-50 text-teal-800 ring-teal-200',
    'warning' => 'bg-amber-50 text-amber-900 ring-amber-200',
    'fail' => 'bg-red-50 text-red-800 ring-red-200',
][$status] ?? 'bg-stone-100 text-stone-700 ring-stone-200')

<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-extrabold ring-1 ring-inset {{ $classes }}">{{ str($status)->headline() }}</span>
