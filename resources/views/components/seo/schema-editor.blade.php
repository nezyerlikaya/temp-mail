@props(['record', 'schemaTypes', 'canUpdate' => false])

<x-admin.card title="Schema JSON-LD" description="JSON-LD is validated and stored as structured data. Executable code is rejected.">
    <div class="space-y-4">
        <div>
            <label for="seo-schema-type" class="text-sm font-extrabold text-stone-800">Schema type</label>
            <select id="seo-schema-type" name="schema_type" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @disabled(! $canUpdate)>
                @foreach ($schemaTypes as $value => $label)
                    <option value="{{ $value }}" @selected(old('schema_type', $record->schema_type) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="seo-schema-json" class="text-sm font-extrabold text-stone-800">JSON-LD</label>
            <textarea id="seo-schema-json" name="schema_json_text" rows="8" class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-3 font-mono text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @disabled(! $canUpdate) @error('schema_json_text') aria-invalid="true" aria-describedby="seo-schema-json-error" @enderror>{{ old('schema_json_text', $record->schema_json ? json_encode($record->schema_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '') }}</textarea>
            @error('schema_json_text')
                <p id="seo-schema-json-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
            @else
                <p class="mt-2 text-xs font-bold text-stone-500">Use a JSON object only. Scripts, inline JavaScript, and HTML execution are not allowed.</p>
            @enderror
        </div>
    </div>
</x-admin.card>
