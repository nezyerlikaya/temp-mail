@props(['value' => null])

<div>
    <div class="flex items-center justify-between gap-3">
        <label for="page-slug" class="text-sm font-extrabold text-stone-950">Slug</label>
        <button type="button" class="text-xs font-extrabold text-teal-700 underline-offset-4 hover:underline focus:outline-none focus:ring-4 focus:ring-teal-600/20" x-on:click="generateSlug()">
            Generate
        </button>
    </div>
    <div class="mt-2 flex rounded-lg border border-stone-300 bg-white focus-within:border-teal-600 focus-within:ring-4 focus-within:ring-teal-600/20">
        <span class="inline-flex items-center border-r border-stone-200 px-3 font-mono text-xs font-bold text-stone-500">/</span>
        <input
            id="page-slug"
            name="slug"
            x-model="slug"
            value="{{ $value }}"
            inputmode="url"
            autocomplete="off"
            placeholder="auto-from-title"
            class="min-w-0 flex-1 rounded-r-lg border-0 px-3 py-2 font-mono text-sm text-stone-950 focus:outline-none focus:ring-0"
            @error('slug') aria-invalid="true" aria-describedby="page-slug-error" @else aria-describedby="page-slug-help" @enderror
        >
    </div>
    @error('slug')
        <p id="page-slug-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
    @else
        <p id="page-slug-help" class="mt-2 text-xs font-bold text-stone-500">Lowercase letters, numbers, and hyphens only. Unique per language.</p>
    @enderror
</div>
