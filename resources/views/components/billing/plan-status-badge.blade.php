@props(['active'])
<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-extrabold {{ $active ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-100 text-stone-700' }}">
    {{ $active ? 'Active' : 'Passive' }}
</span>
