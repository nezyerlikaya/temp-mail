@props(['source'])
<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full bg-teal-50 px-2.5 py-1 text-xs font-extrabold text-teal-800 ring-1 ring-teal-100']) }}>{{ str($source)->replace('_', ' ')->headline() }}</span>
