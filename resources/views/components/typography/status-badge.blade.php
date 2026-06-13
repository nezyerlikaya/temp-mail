@props(['active' => false])

<span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-extrabold ring-1 {{ $active ? 'bg-emerald-50 text-emerald-900 ring-emerald-100' : 'bg-stone-100 text-stone-600 ring-stone-200' }}">
    {{ $active ? 'Active' : 'Passive' }}
</span>
