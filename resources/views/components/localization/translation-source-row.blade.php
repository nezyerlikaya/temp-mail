@props(['source', 'groups', 'canManage' => false])

@php
    $fieldBase = 'mt-1 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm font-semibold text-stone-900 shadow-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20';
    $textareaBase = 'mt-1 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm font-semibold text-stone-900 shadow-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20';
@endphp

<article {{ $attributes->merge(['class' => 'rounded-lg border border-stone-200 bg-white p-4 shadow-sm']) }} x-data="{ editing: false }">
    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <div class="min-w-0 space-y-3">
            <div class="flex flex-wrap items-center gap-2">
                <x-localization.translation-key-badge :key-name="$source->translation_key" />
                <x-localization.translation-status-badge :status="$source->is_active ? 'active' : 'passive'" />
                <x-localization.translation-status-badge :status="$source->is_required ? 'required' : 'optional'" />
                <x-localization.translation-status-badge :status="$source->value_type" />
            </div>

            <div>
                <p class="text-sm font-extrabold text-stone-950">{{ $source->source_value }}</p>
                <p class="mt-1 text-sm leading-6 text-stone-600">{{ $source->description ?: 'No context provided.' }}</p>
            </div>

            <dl class="grid gap-3 text-sm sm:grid-cols-3">
                <div>
                    <dt class="text-xs font-extrabold uppercase tracking-wide text-stone-500">Group</dt>
                    <dd class="mt-1 font-bold text-stone-800">{{ $groups[$source->group_key] ?? str($source->group_key)->headline() }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-extrabold uppercase tracking-wide text-stone-500">Sort order</dt>
                    <dd class="mt-1 font-bold text-stone-800">{{ $source->sort_order }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-extrabold uppercase tracking-wide text-stone-500">Canonical language</dt>
                    <dd class="mt-1 font-bold text-stone-800">English</dd>
                </div>
            </dl>
        </div>

        @if ($canManage)
            <button
                type="button"
                class="inline-flex min-h-10 items-center justify-center rounded-lg border border-stone-300 px-4 py-2 text-sm font-extrabold text-stone-700 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
                x-on:click="editing = ! editing"
                x-bind:aria-expanded="editing.toString()"
            >
                Edit source
            </button>
        @endif
    </div>

    @if ($canManage)
        <form
            method="POST"
            action="{{ route('admin.translation-center.sources.update', $source) }}"
            class="mt-5 border-t border-stone-200 pt-5"
            x-show="editing"
            x-cloak
            x-on:submit="$el.classList.add('pointer-events-none', 'opacity-70'); $el.setAttribute('aria-busy', 'true')"
        >
            @csrf
            @method('PUT')

            <div class="grid gap-4 lg:grid-cols-2">
                <div>
                    <label for="source-{{ $source->id }}-group" class="text-sm font-extrabold text-stone-800">Group</label>
                    <select id="source-{{ $source->id }}-group" name="group_key" class="{{ $fieldBase }}">
                        @foreach ($groups as $key => $label)
                            <option value="{{ $key }}" @selected(old('group_key', $source->group_key) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="source-{{ $source->id }}-key" class="text-sm font-extrabold text-stone-800">Translation key</label>
                    <input id="source-{{ $source->id }}-key" name="translation_key" value="{{ old('translation_key', $source->translation_key) }}" class="{{ $fieldBase }}" autocomplete="off" spellcheck="false">
                </div>
                <div class="lg:col-span-2">
                    <label for="source-{{ $source->id }}-value" class="text-sm font-extrabold text-stone-800">English source value</label>
                    <textarea id="source-{{ $source->id }}-value" name="source_value" rows="3" class="{{ $textareaBase }}">{{ old('source_value', $source->source_value) }}</textarea>
                </div>
                <div class="lg:col-span-2">
                    <label for="source-{{ $source->id }}-description" class="text-sm font-extrabold text-stone-800">Description/context</label>
                    <textarea id="source-{{ $source->id }}-description" name="description" rows="2" class="{{ $textareaBase }}">{{ old('description', $source->description) }}</textarea>
                </div>
                <div>
                    <label for="source-{{ $source->id }}-type" class="text-sm font-extrabold text-stone-800">Value type</label>
                    <select id="source-{{ $source->id }}-type" name="value_type" class="{{ $fieldBase }}">
                        @foreach (['short_text' => 'Short text', 'long_text' => 'Long text', 'rich_text' => 'Rich text readiness', 'boolean' => 'Boolean/readiness'] as $type => $label)
                            <option value="{{ $type }}" @selected(old('value_type', $source->value_type) === $type)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="source-{{ $source->id }}-sort" class="text-sm font-extrabold text-stone-800">Sort order</label>
                    <input id="source-{{ $source->id }}-sort" name="sort_order" type="number" min="0" value="{{ old('sort_order', $source->sort_order) }}" class="{{ $fieldBase }}">
                </div>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-4">
                <input type="hidden" name="is_required" value="0">
                <label class="inline-flex items-center gap-2 text-sm font-bold text-stone-700">
                    <input type="checkbox" name="is_required" value="1" @checked(old('is_required', $source->is_required)) class="h-4 w-4 rounded border-stone-300 text-teal-700 focus:ring-4 focus:ring-teal-600/20">
                    Required source
                </label>
                <input type="hidden" name="is_active" value="0">
                <label class="inline-flex items-center gap-2 text-sm font-bold text-stone-700">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $source->is_active)) class="h-4 w-4 rounded border-stone-300 text-teal-700 focus:ring-4 focus:ring-teal-600/20">
                    Active
                </label>
                <button type="submit" class="ml-auto inline-flex min-h-11 items-center rounded-lg bg-teal-700 px-4 py-2 text-sm font-extrabold text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-600/25">
                    Save source
                </button>
            </div>
        </form>
    @endif
</article>
