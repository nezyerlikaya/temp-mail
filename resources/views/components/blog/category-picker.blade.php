@props(['categories' => collect(), 'selected' => null])

<div>
    <label for="blog-category-id" class="text-sm font-extrabold text-stone-950">Category</label>
    <select
        id="blog-category-id"
        name="blog_category_id"
        class="mt-2 w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
        @error('blog_category_id') aria-invalid="true" aria-describedby="blog-category-id-error" @else aria-describedby="blog-category-id-help" @enderror
    >
        <option value="">No category</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected((string) $selected === (string) $category->id)>{{ $category->name }} ({{ $category->locale?->locale ?? 'unknown' }})</option>
        @endforeach
    </select>
    @error('blog_category_id')
        <p id="blog-category-id-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
    @else
        <p id="blog-category-id-help" class="mt-2 text-xs font-bold text-stone-500">Full taxonomy management arrives in the next Blog Studio step.</p>
    @enderror
</div>
