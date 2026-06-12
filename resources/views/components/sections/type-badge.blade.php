@props(['type', 'types' => []])

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full border border-teal-200 bg-teal-50 px-2.5 py-1 text-xs font-extrabold text-teal-800']) }}>
    {{ $types[$type] ?? str($type)->headline() }}
</span>
