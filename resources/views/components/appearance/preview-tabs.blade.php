@props(['modes'])

<div class="flex flex-wrap gap-2" role="tablist" aria-label="Preview mode">
    @foreach ($modes as $value => $label)
        <button type="button" role="tab" x-on:click="mode = '{{ $value }}'" x-bind:aria-selected="(mode === '{{ $value }}').toString()" class="inline-flex min-h-9 items-center rounded-md px-3 text-sm font-extrabold transition focus:outline-none focus:ring-4 focus:ring-teal-700/20" x-bind:class="mode === '{{ $value }}' ? 'bg-stone-950 text-white' : 'bg-stone-100 text-stone-700 hover:bg-stone-200'">{{ $label }}</button>
    @endforeach
</div>
