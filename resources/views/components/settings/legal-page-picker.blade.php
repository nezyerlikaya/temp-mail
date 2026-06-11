@props(['name', 'page'])

<div class="rounded-md border border-stone-200 p-4">
    <div class="flex items-center justify-between gap-3">
        <label for="{{ $name }}" class="text-sm font-extrabold text-stone-950">{{ $page['label'] }}</label>
        <span class="text-xs font-bold {{ $page['connected'] ? 'text-emerald-700' : 'text-amber-800' }}">{{ $page['connected'] ? 'Connected' : 'Page Studio hook' }}</span>
    </div>
    <input id="{{ $name }}" name="{{ $name }}" type="number" min="1" value="{{ old($name, $page['page_id']) }}" class="mt-3 block w-full rounded-md border border-stone-300 bg-white px-3 py-2.5 text-sm text-stone-950 outline-none focus:border-teal-700 focus:ring-4 focus:ring-teal-700/15" placeholder="Page ID when Page Studio is available" aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}" aria-describedby="{{ $errors->has($name) ? $name.'-error' : '' }}">
    @error($name)<p id="{{ $name }}-error" class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
    <a href="{{ route('admin.page-studio.index') }}" class="mt-3 inline-flex min-h-9 items-center justify-center rounded-md border border-stone-300 px-3 text-sm font-bold text-stone-700 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-700/20">Open Page Studio hook</a>
</div>
