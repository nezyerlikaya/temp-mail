<x-admin.layout title="Typography Center" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Brand"
        title="Typography Center"
        description="Manage public website font stacks across global, theme, and locale scopes without changing admin panel typography."
    />

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif

    <x-error-summary />

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
        <div class="space-y-6">
            <x-admin.card title="Font Registry" description="Safe build-managed, self-hosted, and system font entries. No external font CDN is injected here.">
                @if ($families->isEmpty())
                    <x-typography.empty-state title="No font families yet" description="The registry will seed safe presets automatically." />
                @else
                    <div class="grid gap-4 lg:grid-cols-2">
                        @foreach ($families as $family)
                            <x-typography.font-card
                                :family="$family"
                                :providers="$providers"
                                :can-manage="$canManageFamilies"
                                :font-display-options="$fontDisplayOptions"
                            />
                        @endforeach
                    </div>
                @endif
            </x-admin.card>

            <x-typography.assignment-panel
                title="Global Assignment"
                description="Baseline public font stack used when no theme or locale override exists."
                scope="global"
                scope-key="default"
                :usage-scopes="$usageScopes"
                :families="$activeFamilies"
                :assignments="$assignments"
                :can-manage="$canManageAssignments"
            />

            <x-typography.assignment-panel
                title="Theme Assignment"
                description="Overrides the global stack for the selected public theme."
                scope="theme"
                :scope-key="$selectedTheme"
                :usage-scopes="$usageScopes"
                :families="$activeFamilies"
                :assignments="$assignments"
                :themes="$themes"
                :selected-theme="$selectedTheme"
                :active-theme="$activeTheme"
                :can-manage="$canManageAssignments"
            />

            <x-admin.card title="Locale Overrides" description="Language-specific overrides win over theme and global font assignments.">
                @if ($locales->isEmpty())
                    <x-typography.empty-state title="No locales registered" description="Locale Launch Center must seed locales before locale typography overrides can be configured." />
                @else
                    <div class="mb-4 flex flex-wrap gap-2">
                        @foreach ($locales as $locale)
                            <a href="{{ route('admin.typography-center.index', ['theme' => $selectedTheme, 'locale' => $locale->locale]) }}" class="inline-flex min-h-10 items-center gap-2 rounded-md border px-3 py-2 text-sm font-extrabold transition focus:outline-none focus:ring-4 focus:ring-teal-700/20 {{ $selectedLocale === $locale->locale ? 'border-teal-700 bg-teal-50 text-teal-950' : 'border-stone-200 bg-white text-stone-700 hover:bg-stone-50' }}">
                                {{ strtoupper($locale->locale) }}
                                <span class="text-xs font-bold text-stone-500">{{ strtoupper($locale->direction) }}</span>
                            </a>
                        @endforeach
                    </div>

                    @if ($selectedLocaleModel)
                        <x-typography.locale-override-row
                            :locale="$selectedLocaleModel"
                            :usage-scopes="$usageScopes"
                            :families="$activeFamilies"
                            :assignments="$assignments"
                            :warnings="$coverageWarnings"
                            :can-manage="$canManageAssignments"
                        />
                    @endif
                @endif
            </x-admin.card>
        </div>

        <aside class="space-y-6">
            <x-admin.card title="Resolved Stack" description="Runtime CSS variables generated from locale, theme, then global priority.">
                <div class="space-y-4">
                    @foreach (($localeResolved ?? $globalResolved)['stacks'] as $stack)
                        <div class="rounded-md border border-stone-200 bg-stone-50 p-3">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm font-extrabold text-stone-950">{{ strtoupper($stack['usage']) }}</p>
                                <span class="rounded-full bg-white px-2 py-1 text-xs font-extrabold text-stone-600 ring-1 ring-stone-200">{{ ucfirst($stack['source']) }}</span>
                            </div>
                            <code class="mt-2 block break-words text-xs font-bold text-stone-700">{{ $stack['stack'] }}</code>
                        </div>
                    @endforeach
                </div>
            </x-admin.card>

            <x-admin.card title="CSS Variables" description="Public runtime output. Admin layout keeps its own typography.">
                <pre class="overflow-x-auto rounded-md bg-stone-950 p-4 text-xs font-bold leading-6 text-stone-100">{{ ($localeResolved ?? $globalResolved)['inline_style'] }}</pre>
            </x-admin.card>

            <x-admin.card title="Scope Guard" description="Typography Center controls public font stacks only.">
                <ul class="space-y-3 text-sm font-semibold text-stone-700">
                    <li class="flex gap-2"><i data-lucide="check" class="mt-0.5 size-4 text-teal-700" aria-hidden="true"></i><span>No admin typography changes.</span></li>
                    <li class="flex gap-2"><i data-lucide="check" class="mt-0.5 size-4 text-teal-700" aria-hidden="true"></i><span>No hardcoded Google Fonts CDN links.</span></li>
                    <li class="flex gap-2"><i data-lucide="check" class="mt-0.5 size-4 text-teal-700" aria-hidden="true"></i><span>Assignments are allowlisted and audit logged.</span></li>
                </ul>
            </x-admin.card>
        </aside>
    </div>
</x-admin.layout>
