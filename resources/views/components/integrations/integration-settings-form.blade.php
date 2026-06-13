@props(['integration', 'environment', 'canConfigure' => false, 'canToggle' => false, 'canReveal' => false])

<form method="POST" action="{{ route('admin.integrations.update', $integration['key']) }}" class="space-y-4" x-data="{ busy: false }" x-on:submit="busy = true" x-bind:aria-busy="busy">
    @csrf
    @method('PUT')
    <input type="hidden" name="environment" value="{{ $environment }}">

    <div class="grid gap-4">
        @foreach ($integration['fields'] as $field)
            @if ($field['type'] === 'secret')
                <x-integrations.secret-field :field="$field" :value="$integration['masked_secrets'][$field['key']] ?? null" :integration="$integration" :environment="$environment" :can-reveal="$canReveal" />
            @elseif ($field['type'] === 'boolean')
                <label class="flex items-center justify-between gap-3 rounded-md border border-stone-200 p-3 text-sm font-bold text-stone-900">
                    <span>{{ $field['label'] }}</span>
                    <input type="checkbox" name="settings[{{ $field['key'] }}]" value="1" @checked(old('settings.'.$field['key'], $integration['payload'][$field['key']] ?? false)) class="size-4 rounded border-stone-300 text-teal-700 focus:ring-teal-700">
                </label>
            @elseif ($field['type'] === 'select')
                <label class="grid gap-2 text-sm font-bold text-stone-900">
                    <span>{{ $field['label'] }}</span>
                    <select name="settings[{{ $field['key'] }}]" class="min-h-11 rounded-md border border-stone-300 bg-white px-3 text-sm font-bold text-stone-900 focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-700/20">
                        @foreach ($field['options'] as $option)
                            <option value="{{ $option }}" @selected(old('settings.'.$field['key'], $integration['payload'][$field['key']] ?? '') === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </label>
            @else
                <label class="grid gap-2 text-sm font-bold text-stone-900">
                    <span>{{ $field['label'] }}</span>
                    <input type="{{ $field['type'] === 'url' ? 'url' : ($field['type'] === 'email' ? 'email' : 'text') }}" name="settings[{{ $field['key'] }}]" value="{{ old('settings.'.$field['key'], $integration['payload'][$field['key']] ?? '') }}" class="min-h-11 rounded-md border border-stone-300 bg-white px-3 text-sm font-bold text-stone-900 focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-700/20">
                </label>
            @endif
        @endforeach
    </div>

    <x-integrations.save-bar :can-save="$canConfigure" />
</form>

@if ($canToggle)
    <form method="POST" action="{{ $integration['is_active'] ? route('admin.integrations.deactivate', $integration['key']) : route('admin.integrations.activate', $integration['key']) }}" class="mt-4">
        @csrf
        <input type="hidden" name="environment" value="{{ $environment }}">
        <button type="submit" class="inline-flex min-h-10 items-center gap-2 rounded-md border border-stone-300 bg-white px-3 py-2 text-sm font-extrabold text-stone-800 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-700/20">
            <i data-lucide="{{ $integration['is_active'] ? 'pause-circle' : 'play-circle' }}" class="size-4" aria-hidden="true"></i>
            {{ $integration['is_active'] ? 'Deactivate' : 'Activate' }}
        </button>
    </form>
@endif
