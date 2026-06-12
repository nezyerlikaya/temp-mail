@props(['label', 'value' => '', 'idealMin', 'idealMax'])

<p class="mt-2 text-xs font-bold" x-bind:class="({{ strlen((string) $value) }} >= {{ $idealMin }} && {{ strlen((string) $value) }} <= {{ $idealMax }}) ? 'text-emerald-700' : 'text-stone-500'">
    {{ $label }} ideal: {{ $idealMin }}-{{ $idealMax }} characters.
</p>
