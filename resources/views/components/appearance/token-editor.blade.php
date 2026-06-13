@props([
    'selectedTheme',
    'setting',
    'tokenDefinitions',
    'draftTokens',
    'defaultTokens',
    'radiusOptions',
    'shadowOptions',
    'motionOptions',
    'canUpdate' => false,
    'canReset' => false,
])

<section
    class="rounded-lg border border-stone-200 bg-white shadow-sm"
    x-data="{ dirty: false, submitting: false }"
    x-on:beforeunload.window="if (dirty && !submitting) { $event.preventDefault(); $event.returnValue = ''; }"
>
    <div class="flex flex-col gap-3 border-b border-stone-200 p-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-xl font-extrabold text-stone-950">{{ str($selectedTheme)->headline() }} tokens</h2>
                <x-appearance.status-badge :mode="$setting->mode" />
            </div>
            <p class="mt-1 text-sm leading-6 text-stone-600">Draft values are safe public CSS variables. Publishing preview is intentionally reserved for the next Appearance step.</p>
        </div>
    </div>

    <form id="appearance-editor" method="POST" action="{{ route('admin.appearance-studio.update') }}" class="p-5" x-on:submit="submitting = true" novalidate>
        @csrf
        @method('PUT')
        <input type="hidden" name="theme" value="{{ $selectedTheme }}">

        <fieldset class="mb-5 rounded-md border border-stone-200 bg-stone-50 p-4">
            <legend class="text-sm font-extrabold text-stone-950">Appearance mode</legend>
            <div class="mt-3 grid gap-2 sm:grid-cols-2">
                <label class="flex items-start gap-3 rounded-md border border-stone-200 bg-white p-3 has-[:checked]:border-teal-700 has-[:checked]:bg-teal-50">
                    <input type="radio" name="mode" value="defaults" class="mt-1 size-4 text-teal-700 focus:ring-4 focus:ring-teal-700/20" @checked(old('mode', $setting->mode) === 'defaults') x-on:change="dirty = true">
                    <span><span class="block text-sm font-extrabold text-stone-950">Use theme defaults</span><span class="mt-1 block text-xs font-semibold text-stone-500">Keep the selected theme preset as the source of truth.</span></span>
                </label>
                <label class="flex items-start gap-3 rounded-md border border-stone-200 bg-white p-3 has-[:checked]:border-teal-700 has-[:checked]:bg-teal-50">
                    <input type="radio" name="mode" value="custom" class="mt-1 size-4 text-teal-700 focus:ring-4 focus:ring-teal-700/20" @checked(old('mode', $setting->mode) === 'custom') x-on:change="dirty = true">
                    <span><span class="block text-sm font-extrabold text-stone-950">Custom appearance mode</span><span class="mt-1 block text-xs font-semibold text-stone-500">Store safe draft values for this theme only.</span></span>
                </label>
            </div>
        </fieldset>

        <div class="grid gap-4 lg:grid-cols-2">
            @foreach ($tokenDefinitions as $name => $definition)
                @if ($definition['type'] === 'color')
                    <x-appearance.color-field :name="$name" :label="$definition['label']" :value="$draftTokens[$name]" :default="$defaultTokens[$name]" />
                @elseif ($definition['type'] === 'radius')
                    <x-appearance.radius-control :name="$name" :label="$definition['label']" :value="$draftTokens[$name]" :default="$defaultTokens[$name]" :options="$radiusOptions" />
                @elseif ($definition['type'] === 'shadow')
                    <x-appearance.shadow-control :name="$name" :label="$definition['label']" :value="$draftTokens[$name]" :default="$defaultTokens[$name]" :options="$shadowOptions" />
                @elseif ($definition['type'] === 'motion')
                    <x-appearance.motion-control :name="$name" :label="$definition['label']" :value="$draftTokens[$name]" :default="$defaultTokens[$name]" :options="$motionOptions" />
                @endif
            @endforeach
        </div>

        <x-appearance.save-bar :can-update="$canUpdate" />
    </form>

    @foreach ($tokenDefinitions as $name => $definition)
        <form id="reset-token-{{ $name }}" method="POST" action="{{ route('admin.appearance-studio.reset-token') }}">
            @csrf
            <input type="hidden" name="theme" value="{{ $selectedTheme }}">
            <input type="hidden" name="token" value="{{ $name }}">
        </form>
    @endforeach
</section>
