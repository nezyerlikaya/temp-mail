@props(['field', 'value' => null, 'integration', 'environment', 'canReveal' => false])

@php($id = 'secret-'.$integration['key'].'-'.$field['key'])

<div class="grid gap-2">
    <label for="{{ $id }}" class="text-sm font-bold text-stone-900">{{ $field['label'] }}</label>
    <input id="{{ $id }}" type="password" name="secrets[{{ $field['key'] }}]" value="" placeholder="{{ $value ? $value.' - replace only' : 'Enter secret' }}" autocomplete="new-password" aria-describedby="{{ $id }}-hint" class="min-h-11 rounded-md border border-stone-300 bg-white px-3 text-sm font-bold text-stone-900 focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-700/20">
    <p id="{{ $id }}-hint" class="text-xs font-semibold text-stone-500">Stored encrypted. Leave blank to preserve the current secret.</p>
    @if ($canReveal && ($field['reveal'] ?? true) && $value)
        <a href="{{ route('admin.integrations.secrets.reveal', ['integration' => $integration['key'], 'field' => $field['key'], 'environment' => $environment]) }}" class="text-xs font-extrabold text-teal-800 underline decoration-teal-700/30 underline-offset-4">Owner reveal endpoint</a>
    @endif
</div>
