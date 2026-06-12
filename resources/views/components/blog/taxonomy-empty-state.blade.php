@props(['type' => 'records'])

<div {{ $attributes->merge(['class' => 'rounded-lg border border-dashed border-stone-300 bg-white p-8 text-center']) }}>
    <p class="text-sm font-extrabold uppercase tracking-wide text-teal-700">No {{ $type }} yet</p>
    <h2 class="mt-2 text-xl font-extrabold text-stone-950">Create language-specific taxonomy when the editorial map is ready.</h2>
    <p class="mx-auto mt-2 max-w-xl text-sm text-stone-600">Categories and tags stay attached to one language, so Blog Studio never mixes records across locales.</p>
</div>
