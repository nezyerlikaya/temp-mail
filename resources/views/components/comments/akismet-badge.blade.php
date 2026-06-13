@props(['provider', 'decision'])

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full border border-sky-200 bg-sky-50 px-2.5 py-1 text-xs font-extrabold text-sky-800']) }}>
    {{ $provider ? str($provider)->headline() : 'Manual' }}: {{ str((string) ($decision ?: 'not checked'))->headline() }}
</span>
