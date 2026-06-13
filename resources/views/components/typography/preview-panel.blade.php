@props(['preview', 'samples' => [], 'modes' => [], 'directions' => [], 'selectedTheme' => null, 'selectedLocale' => null])

<x-admin.card title="Typography Preview" description="Multilingual public UI preview with resolved stacks and LTR/RTL controls.">
    <form method="GET" action="{{ route('admin.typography-center.index') }}" class="mb-5 grid gap-3 lg:grid-cols-[1fr_auto]" x-data="{ busy: false }" x-on:change.debounce.150ms="$el.requestSubmit()" x-on:submit="busy = true" x-bind:aria-busy="busy">
        <input type="hidden" name="theme" value="{{ $selectedTheme }}">
        @if ($selectedLocale)
            <input type="hidden" name="locale" value="{{ $selectedLocale }}">
        @endif

        <div class="grid gap-3 md:grid-cols-3">
            <x-typography.preview-language-tabs :samples="$samples" :selected="$preview['language']" />
            <x-typography.device-preview-tabs :modes="$modes" :selected="$preview['mode']" />
            <label class="grid gap-2 text-sm font-bold text-stone-700">
                <span>Direction</span>
                <select name="preview_direction" class="min-h-11 rounded-md border border-stone-300 bg-white px-3 text-sm font-bold text-stone-900 focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-700/20">
                    @foreach ($directions as $value => $label)
                        <option value="{{ $value }}" @selected($preview['direction'] === $value || request('preview_direction', 'auto') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
        </div>

        <button type="submit" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-md bg-stone-950 px-4 py-2 text-sm font-extrabold text-white transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-stone-950/20">
            <i data-lucide="refresh-cw" class="size-4" aria-hidden="true"></i>
            <span x-text="busy ? 'Updating...' : 'Update preview'">Update preview</span>
        </button>
    </form>

    <div dir="{{ $preview['direction'] }}" class="rounded-lg border border-stone-200 bg-stone-50 p-4 {{ $preview['mode'] === 'mobile' ? 'max-w-sm' : '' }}" style="{{ $preview['resolved']['inline_style'] }}">
        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($preview['cards'] as $scope => $card)
                @php($fontUsage = in_array($scope, ['mailbox', 'cta', 'faq'], true) ? 'ui' : $scope)
                <section class="rounded-lg border border-stone-200 bg-white p-4">
                    <p class="text-xs font-extrabold uppercase text-stone-500">{{ $card['label'] }}</p>
                    <p class="mt-2 text-base font-bold leading-7 text-stone-950" style="font-family: var(--tm-font-{{ $fontUsage }});">{{ $card['text'] }}</p>
                    <code class="mt-3 block break-words text-xs font-bold text-stone-500">{{ $preview['resolved']['variables']['--tm-font-'.$fontUsage] ?? '' }}</code>
                </section>
            @endforeach
        </div>
    </div>
</x-admin.card>
