@props(['settings' => []])

<div x-show="sectionType === 'cta'" class="space-y-4 rounded-lg border border-stone-200 bg-stone-50 p-4">
    <div>
        <label for="cta-button-label" class="text-sm font-extrabold text-stone-800">Button label</label>
        <input id="cta-button-label" name="settings[button_label]" value="{{ old('settings.button_label', $settings['button_label'] ?? '') }}" type="text" x-bind:disabled="sectionType !== 'cta'" class="mt-2 block min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20" x-on:input="dirty = true">
    </div>
    <div>
        <label for="cta-button-url" class="text-sm font-extrabold text-stone-800">Button URL or page readiness</label>
        <input id="cta-button-url" name="settings[button_url]" value="{{ old('settings.button_url', $settings['button_url'] ?? '') }}" type="text" inputmode="url" placeholder="/en/pricing" x-bind:disabled="sectionType !== 'cta'" class="mt-2 block min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20" x-on:input="dirty = true">
        <p class="mt-2 text-xs font-bold text-stone-500">Full internal page picker is prepared for a later step.</p>
    </div>
</div>
