@props(['category' => null, 'locales', 'statuses', 'action', 'method' => 'POST'])

@php
    $isEditing = filled($category);
    $id = $isEditing ? 'category-'.$category->id : 'category-new';
@endphp

<form method="POST" action="{{ $action }}" class="space-y-4" x-data="{ submitting: false }" x-on:submit="if (submitting) { $event.preventDefault(); return; } submitting = true" x-bind:aria-busy="submitting.toString()" x-bind:class="{ 'pointer-events-none opacity-70': submitting }">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div>
        <label for="{{ $id }}-locale" class="text-sm font-extrabold text-stone-800">Language</label>
        <select
            id="{{ $id }}-locale"
            name="locale_id"
            class="mt-2 block min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm font-bold text-stone-950 shadow-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
            @error('locale_id') aria-invalid="true" aria-describedby="{{ $id }}-locale-error" @enderror
        >
            <option value="">Choose language</option>
            @foreach ($locales as $locale)
                <option value="{{ $locale->id }}" @selected((string) old('locale_id', $category?->locale_id) === (string) $locale->id)>{{ $locale->language_name }}</option>
            @endforeach
        </select>
        @error('locale_id') <p id="{{ $id }}-locale-error" class="mt-2 text-sm font-bold text-red-700">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="{{ $id }}-name" class="text-sm font-extrabold text-stone-800">Name</label>
        <input id="{{ $id }}-name" name="name" value="{{ old('name', $category?->name) }}" type="text" autocomplete="off" class="mt-2 block min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm text-stone-950 shadow-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @error('name') aria-invalid="true" aria-describedby="{{ $id }}-name-error" @enderror>
        @error('name') <p id="{{ $id }}-name-error" class="mt-2 text-sm font-bold text-red-700">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="{{ $id }}-slug" class="text-sm font-extrabold text-stone-800">Slug</label>
        <input id="{{ $id }}-slug" name="slug" value="{{ old('slug', $category?->slug) }}" type="text" inputmode="url" autocomplete="off" class="mt-2 block min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm text-stone-950 shadow-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @error('slug') aria-invalid="true" aria-describedby="{{ $id }}-slug-error" @enderror>
        @error('slug') <p id="{{ $id }}-slug-error" class="mt-2 text-sm font-bold text-red-700">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="{{ $id }}-description" class="text-sm font-extrabold text-stone-800">Description</label>
        <textarea id="{{ $id }}-description" name="description" rows="4" class="mt-2 block w-full rounded-lg border border-stone-300 bg-white px-3 py-3 text-sm text-stone-950 shadow-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @error('description') aria-invalid="true" aria-describedby="{{ $id }}-description-error" @enderror>{{ old('description', $category?->description) }}</textarea>
        @error('description') <p id="{{ $id }}-description-error" class="mt-2 text-sm font-bold text-red-700">{{ $message }}</p> @enderror
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="{{ $id }}-status" class="text-sm font-extrabold text-stone-800">Status</label>
            <select id="{{ $id }}-status" name="status" class="mt-2 block min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm font-bold text-stone-950 shadow-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $category?->status ?? 'active') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="{{ $id }}-sort-order" class="text-sm font-extrabold text-stone-800">Sort order</label>
            <input id="{{ $id }}-sort-order" name="sort_order" value="{{ old('sort_order', $category?->sort_order ?? 0) }}" type="number" min="0" inputmode="numeric" class="no-spinner mt-2 block min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm text-stone-950 shadow-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @error('sort_order') aria-invalid="true" aria-describedby="{{ $id }}-sort-order-error" @enderror>
            @error('sort_order') <p id="{{ $id }}-sort-order-error" class="mt-2 text-sm font-bold text-red-700">{{ $message }}</p> @enderror
        </div>
    </div>

    <button type="submit" x-bind:disabled="submitting" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-stone-950 px-4 text-sm font-extrabold text-white shadow-sm transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/25 disabled:cursor-not-allowed disabled:opacity-70">
        <span x-text="submitting ? 'Saving...' : '{{ $isEditing ? 'Update category' : 'Create category' }}'"></span>
    </button>
</form>
