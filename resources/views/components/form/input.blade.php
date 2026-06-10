@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'help' => null,
    'autocomplete' => null,
    'inputmode' => null,
])

@php
    $id = $attributes->get('id', $name);
    $errorId = $id.'-error';
    $helpId = $id.'-help';
    $invalid = $errors->has($name);
@endphp

<div>
    <label for="{{ $id }}" class="block text-sm font-bold text-stone-900">{{ $label }}</label>
    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        @if ($inputmode) inputmode="{{ $inputmode }}" @endif
        aria-invalid="{{ $invalid ? 'true' : 'false' }}"
        aria-describedby="{{ trim(($help ? $helpId : '').' '.($invalid ? $errorId : '')) }}"
        {{ $attributes->except('id')->merge(['class' => 'mt-2 block w-full rounded-lg border border-stone-300 bg-white px-4 py-3 text-base text-stone-950 shadow-sm outline-none transition placeholder:text-stone-400 focus:border-teal-700 focus:ring-4 focus:ring-teal-700/15 disabled:cursor-not-allowed disabled:bg-stone-100']) }}
    >
    @if ($help)
        <p id="{{ $helpId }}" class="mt-2 text-sm text-stone-600">{{ $help }}</p>
    @endif
    @error($name)
        <p id="{{ $errorId }}" class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>
    @enderror
</div>
