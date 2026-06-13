@props(['status'])

@php($active = $status === 'active')

<span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-extrabold ring-1 ring-inset {{ $active ? 'bg-teal-50 text-teal-800 ring-teal-200' : 'bg-stone-100 text-stone-700 ring-stone-200' }}">
    <span class="size-1.5 rounded-full {{ $active ? 'bg-teal-700' : 'bg-stone-400' }}" aria-hidden="true"></span>
    {{ str($status)->headline() }}
</span>
