@props(['templates', 'variables', 'targetTypes', 'locales', 'canManage' => false])

<x-admin.card title="SEO templates" description="Reusable defaults for repeated targets. Manual SEO record values always win.">
    <form method="POST" action="{{ route('admin.seo-growth-center.templates.save') }}" class="space-y-4" x-data="{ submitting: false }" x-on:submit="submitting = true" x-bind:aria-busy="submitting.toString()">
        @csrf
        <div class="grid gap-3 md:grid-cols-3">
            <label class="text-sm font-bold text-stone-700">
                <span>Target type</span>
                <select name="target_type" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @error('target_type') aria-invalid="true" @enderror>
                    @foreach ($targetTypes as $value => $label)
                        <option value="{{ $value }}" @selected(old('target_type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="text-sm font-bold text-stone-700">
                <span>Language</span>
                <select name="locale_id" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                    <option value="">Global default</option>
                    @foreach ($locales as $locale)
                        <option value="{{ $locale->id }}" @selected((string) old('locale_id') === (string) $locale->id)>{{ $locale->language_name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="text-sm font-bold text-stone-700">
                <span>Name</span>
                <input name="name" value="{{ old('name', 'Default SEO template') }}" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @error('name') aria-invalid="true" @enderror>
            </label>
        </div>

        <div class="grid gap-3 md:grid-cols-2">
            <label class="text-sm font-bold text-stone-700">
                <span>Title template</span>
                <input name="meta_title_template" value="{{ old('meta_title_template', '{post_title} | {site_name}') }}" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
            </label>
            <label class="text-sm font-bold text-stone-700">
                <span>Description template</span>
                <input name="meta_description_template" value="{{ old('meta_description_template', 'Read {post_title} in {locale_name} on {site_name}.') }}" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
            </label>
        </div>
        <input type="hidden" name="is_active" value="1">
        <button type="submit" @disabled(! $canManage) x-bind:disabled="submitting || {{ $canManage ? 'false' : 'true' }}" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-stone-950 px-4 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:opacity-60">
            <span x-text="submitting ? 'Saving...' : 'Save template'"></span>
        </button>
    </form>

    <div class="mt-4 flex flex-wrap gap-2">
        @foreach ($variables as $variable => $label)
            <span class="rounded-md border border-stone-200 bg-stone-50 px-2 py-1 text-xs font-extrabold text-stone-600">{{ '{'.$variable.'}' }}</span>
        @endforeach
    </div>

    @if ($templates->count() > 0)
        <div class="mt-4 space-y-2">
            @foreach ($templates->take(4) as $template)
                <div class="rounded-lg border border-stone-200 p-3">
                    <p class="text-sm font-extrabold text-stone-950">{{ $template->name }}</p>
                    <p class="mt-1 text-xs font-bold text-stone-500">{{ str($template->target_type)->replace('_', ' ')->headline() }} · {{ $template->locale?->locale ?? 'global' }}</p>
                </div>
            @endforeach
        </div>
    @endif
</x-admin.card>
