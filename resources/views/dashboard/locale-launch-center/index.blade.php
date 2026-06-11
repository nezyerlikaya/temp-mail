<x-admin.layout title="Locale Launch Center" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Markets"
        title="Locale Launch Center"
        description="Prepare language and market readiness without editing translation copy or homepage content."
    >
        <x-slot:actions>
            <x-admin.status-badge status="Readiness" />
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif
    @if (session('warning'))
        <x-admin.alert variant="warning" class="mb-6">{{ session('warning') }}</x-admin.alert>
    @endif

    <x-error-summary />

    <section class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-6" aria-label="Locale launch summary">
        @foreach ([
            ['label' => 'Locales', 'value' => $summary['total']],
            ['label' => 'Active', 'value' => $summary['active']],
            ['label' => 'Passive', 'value' => $summary['passive']],
            ['label' => 'Ready', 'value' => $summary['ready']],
            ['label' => 'RTL', 'value' => $summary['rtl']],
            ['label' => 'Default', 'value' => strtoupper((string) $summary['default_locale'])],
        ] as $metric)
            <div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-bold text-stone-500">{{ $metric['label'] }}</p>
                <p class="mt-2 break-words text-2xl font-extrabold text-stone-950">{{ $metric['value'] }}</p>
            </div>
        @endforeach
    </section>

    @if (count($summary['issues']) > 0)
        <x-admin.alert variant="warning" title="Readiness attention" class="mb-6">
            <ul class="space-y-1">
                @foreach ($summary['issues'] as $issue)
                    <li>{{ $issue }}</li>
                @endforeach
            </ul>
        </x-admin.alert>
    @endif

    <div class="space-y-6" x-data="{ selectAllVisible: false, toggleVisible() { document.querySelectorAll('.js-locale-bulk-checkbox').forEach((input) => input.checked = this.selectAllVisible) } }">
        <x-localization.language-filters :filters="$filters" />

        <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="min-w-0">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-extrabold text-stone-950">Market readiness queue</h2>
                        <p class="mt-1 text-sm text-stone-600">Cards prioritize launch decisions over copy editing.</p>
                    </div>
                    <label class="inline-flex items-center gap-3 rounded-lg border border-stone-200 bg-white px-4 py-3 text-sm font-bold text-stone-700 shadow-sm">
                        <input type="checkbox" x-model="selectAllVisible" x-on:change="toggleVisible()" class="h-4 w-4 rounded border-stone-300 text-teal-700 focus:ring-4 focus:ring-teal-600/20">
                        Select visible
                    </label>
                </div>

                @if ($locales->count() > 0)
                    <form method="POST" action="{{ route('admin.locale-launch-center.update') }}" class="space-y-5">
                        @csrf
                        @method('PUT')

                        <div class="grid gap-4 xl:grid-cols-2">
                            @foreach ($locales as $locale)
                                <x-localization.language-card :locale="$locale" :readiness="$readiness[$locale->locale]" :can-manage="$canManageLocalization" />
                            @endforeach
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <x-admin.pagination :paginator="$locales" class="flex-1" />
                            <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-teal-700 px-5 py-3 text-sm font-extrabold text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-600/25 disabled:cursor-not-allowed disabled:opacity-60" @disabled(! $canManageLocalization)>
                                Save readiness
                            </button>
                        </div>
                    </form>
                @else
                    <x-localization.language-empty-state />
                @endif
            </div>

            <aside class="min-w-0 space-y-6">
                <x-admin.bulk-actions :disabled="! $canManageLocalization" />

                <x-admin.card title="Launch rules" description="Locale Launch Center only controls market readiness and locale availability.">
                    <ul class="space-y-3 text-sm leading-6 text-stone-700">
                        <li>English remains available as the canonical source language.</li>
                        <li>Exactly one default language is allowed.</li>
                        <li>The default language must stay active.</li>
                        <li>Arabic and Hebrew are prepared as RTL locales.</li>
                        <li>Translation copy belongs in Translation Center.</li>
                    </ul>
                </x-admin.card>

                <x-admin.card title="Content boundary" description="This screen intentionally avoids content editing.">
                    <div class="space-y-3 text-sm text-stone-700">
                        <p>No homepage textarea fields.</p>
                        <p>No translation editor fields.</p>
                        <p>No blog, page, section, or email content editing.</p>
                    </div>
                </x-admin.card>
            </aside>
        </div>
    </div>
</x-admin.layout>
