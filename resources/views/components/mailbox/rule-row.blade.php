@props(['name', 'label', 'description', 'value', 'min' => null, 'max' => null, 'suffix' => null, 'canUpdate' => false])

@php
    $errorId = $name.'-error';
    $invalid = $errors->has($name);
@endphp

<label for="{{ $name }}" class="grid gap-3 border-b border-stone-200 py-4 last:border-b-0 lg:grid-cols-[minmax(0,1fr)_220px] lg:items-center">
    <span>
        <span class="block text-sm font-extrabold text-stone-950">{{ $label }}</span>
        <span class="mt-1 block text-sm leading-5 text-stone-600">{{ $description }}</span>
    </span>
    <span class="relative block">
        <input
            id="{{ $name }}"
            name="{{ $name }}"
            type="number"
            value="{{ old($name, $value) }}"
            min="{{ $min }}"
            max="{{ $max }}"
            @disabled(! $canUpdate)
            @if($invalid) aria-invalid="true" aria-describedby="{{ $errorId }}" @endif
            class="min-h-11 w-full rounded-md border border-stone-300 px-3 pr-16 text-sm font-extrabold text-stone-900 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:bg-stone-100"
        >
        @if($suffix)
            <span class="pointer-events-none absolute right-3 top-3.5 text-xs font-bold text-stone-500">{{ $suffix }}</span>
        @endif
        @if($invalid)
            <span id="{{ $errorId }}" class="mt-1 block text-sm font-bold text-red-700" role="alert">{{ $errors->first($name) }}</span>
        @endif
    </span>
</label>
