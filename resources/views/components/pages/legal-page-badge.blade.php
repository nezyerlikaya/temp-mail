@props(['readiness' => null])

@if (($readiness['is_legal'] ?? false) === true)
    <span {{ $attributes->merge(['class' => 'inline-flex rounded-full border px-2.5 py-1 text-xs font-extrabold '.(($readiness['mapped'] ?? false) ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-amber-200 bg-amber-50 text-amber-900')]) }}>
        {{ $readiness['label'] ?? 'Legal page' }} {{ ($readiness['mapped'] ?? false) ? 'mapped' : 'ready' }}
    </span>
@endif
