@props(['secret'])

<x-admin.alert variant="warning" {{ $attributes }}>
    <div x-data="{ copied: false }">
        <p class="font-extrabold">Copy this API secret now. It will not be shown again.</p>
        <div class="mt-3 flex flex-col gap-3 sm:flex-row">
            <code class="min-h-11 flex-1 overflow-x-auto rounded-lg border border-amber-200 bg-white px-3 py-3 text-sm font-extrabold text-stone-950">{{ $secret['secret'] }}</code>
            <button type="button" x-on:click="navigator.clipboard.writeText(@js($secret['secret'])); copied = true" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-stone-950 px-4 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                <span x-show="!copied">Copy</span>
                <span x-cloak x-show="copied">Copied</span>
            </button>
        </div>
        <p class="mt-2 text-sm">Key: {{ $secret['name'] }} · Prefix: {{ $secret['prefix'] }}</p>
    </div>
</x-admin.alert>
