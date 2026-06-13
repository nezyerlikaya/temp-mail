@props(['type'])
<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full bg-stone-100 px-2.5 py-1 text-xs font-extrabold text-stone-800']) }}>{{ str($type)->replace('_', ' ')->headline() }}</span>
