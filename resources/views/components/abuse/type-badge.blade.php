@props(['type'])
<span {{ $attributes->merge(['class' => 'inline-flex rounded-full border border-stone-200 bg-stone-50 px-2.5 py-1 text-xs font-extrabold text-stone-700']) }}>{{ str($type)->replace('_', ' ')->headline() }}</span>
