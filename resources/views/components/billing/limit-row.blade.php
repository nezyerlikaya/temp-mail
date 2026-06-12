@props(['name', 'label', 'description', 'value', 'suffix' => null, 'type' => 'number', 'canUpdate' => false, 'planId' => 'plan'])
@php($id = $name.'-'.$planId)
<label for="{{ $id }}" class="grid gap-3 border-b border-stone-200 py-3 last:border-b-0 lg:grid-cols-[minmax(0,1fr)_220px] lg:items-center">
    <span>
        <span class="block text-sm font-extrabold text-stone-950">{{ $label }}</span>
        <span class="mt-1 block text-sm text-stone-600">{{ $description }}</span>
    </span>
    @if($type === 'checkbox')
        <span class="flex items-center justify-end gap-3">
            <input id="{{ $id }}" name="{{ $name }}" type="checkbox" value="1" @checked(old($name, $value)) @disabled(! $canUpdate) class="size-5 rounded border-stone-300 text-teal-700 focus:ring-4 focus:ring-teal-600/20">
            <span class="text-sm font-bold text-stone-600">{{ old($name, $value) ? 'Allowed' : 'Disabled' }}</span>
        </span>
    @else
        <span class="relative block">
            <input id="{{ $id }}" name="{{ $name }}" type="number" value="{{ old($name, $value) }}" @disabled(! $canUpdate) class="min-h-11 w-full rounded-md border border-stone-300 px-3 pr-16 text-sm font-extrabold text-stone-900 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:bg-stone-100" @error($name) aria-invalid="true" aria-describedby="{{ $id }}-error" @enderror>
            @if($suffix)<span class="pointer-events-none absolute right-3 top-3.5 text-xs font-bold text-stone-500">{{ $suffix }}</span>@endif
            @error($name)<span id="{{ $id }}-error" class="mt-1 block text-sm font-bold text-red-700" role="alert">{{ $message }}</span>@enderror
        </span>
    @endif
</label>
