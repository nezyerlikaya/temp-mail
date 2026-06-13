@props(['devices', 'directions'])

<div class="flex flex-wrap items-center gap-2">
    <div class="flex gap-2" aria-label="Preview device">
        @foreach ($devices as $value => $label)
            <button type="button" x-on:click="device = '{{ $value }}'" class="inline-flex min-h-9 items-center rounded-md px-3 text-sm font-extrabold transition focus:outline-none focus:ring-4 focus:ring-teal-700/20" x-bind:class="device === '{{ $value }}' ? 'bg-teal-700 text-white' : 'bg-stone-100 text-stone-700 hover:bg-stone-200'">{{ $label }}</button>
        @endforeach
    </div>
    <div class="flex gap-2" aria-label="Preview direction">
        @foreach ($directions as $value => $label)
            <button type="button" x-on:click="direction = '{{ $value }}'" class="inline-flex min-h-9 items-center rounded-md px-3 text-sm font-extrabold transition focus:outline-none focus:ring-4 focus:ring-teal-700/20" x-bind:class="direction === '{{ $value }}' ? 'bg-blue-700 text-white' : 'bg-stone-100 text-stone-700 hover:bg-stone-200'">{{ $label }}</button>
        @endforeach
    </div>
</div>
