@props(['ready' => false])

<span class="rounded-full px-2.5 py-1 text-xs font-extrabold ring-1 {{ $ready ? 'bg-emerald-50 text-emerald-900 ring-emerald-100' : 'bg-amber-50 text-amber-900 ring-amber-100' }}">
    {{ $ready ? 'Covered' : 'Risk' }}
</span>
