@props(['provider', 'providers' => []])

<span class="inline-flex items-center rounded-full bg-stone-100 px-2.5 py-1 text-xs font-extrabold text-stone-700 ring-1 ring-stone-200">
    {{ $providers[$provider] ?? str($provider)->replace('_', ' ')->headline() }}
</span>
