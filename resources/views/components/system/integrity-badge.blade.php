@props(['result'])

@php
    $passed = ($result['status'] ?? 'failed') === 'passed';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex min-h-7 items-center gap-1.5 rounded-full border px-2.5 text-xs font-extrabold '.($passed ? 'border-teal-200 bg-teal-50 text-teal-800' : 'border-amber-200 bg-amber-50 text-amber-800')]) }}>
    <i data-lucide="{{ $passed ? 'shield-check' : 'shield-alert' }}" class="size-3.5" aria-hidden="true"></i>
    {{ $passed ? 'Verified' : 'Needs review' }}
</span>
