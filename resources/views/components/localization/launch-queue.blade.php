@props(['queue'])

<section {{ $attributes->merge(['class' => 'rounded-lg border border-stone-200 bg-white shadow-sm']) }} aria-labelledby="launch-queue-title">
    <div class="border-b border-stone-200 px-5 py-4">
        <h2 id="launch-queue-title" class="text-base font-extrabold text-stone-950">Launch queue</h2>
        <p class="mt-1 text-sm text-stone-600">Operational readiness groups for locale launch work.</p>
    </div>
    <div class="grid gap-3 p-5">
        @foreach ($queue as $item)
            <div class="rounded-lg border border-stone-200 bg-stone-50 p-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-extrabold text-stone-950">{{ $item['label'] }}</p>
                        <p class="mt-1 text-sm leading-5 text-stone-600">{{ $item['description'] }}</p>
                    </div>
                    <span class="rounded-full bg-white px-2.5 py-1 text-xs font-extrabold text-stone-700 ring-1 ring-stone-200">{{ $item['count'] }}</span>
                </div>
                @if (count($item['locales']) > 0)
                    <p class="mt-3 text-xs font-bold text-stone-500">{{ implode(', ', $item['locales']) }}</p>
                @endif
            </div>
        @endforeach
    </div>
</section>
