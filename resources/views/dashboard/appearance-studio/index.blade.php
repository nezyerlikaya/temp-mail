<x-admin.layout title="Appearance Studio" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Brand"
        title="Appearance Studio"
        description="Tune safe public theme visual tokens without changing layouts, fonts, admin chrome, or custom CSS."
    />

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif

    <x-error-summary />

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="space-y-6">
            <section class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm" aria-label="Theme selection">
                <div class="flex flex-wrap gap-2">
                    @foreach ($themes as $theme)
                        <a href="{{ route('admin.appearance-studio.index', ['theme' => $theme['slug']]) }}" class="inline-flex min-h-10 items-center gap-2 rounded-md border px-3 py-2 text-sm font-extrabold transition focus:outline-none focus:ring-4 focus:ring-teal-700/20 {{ $selectedTheme === $theme['slug'] ? 'border-teal-700 bg-teal-50 text-teal-900' : 'border-stone-200 bg-white text-stone-700 hover:bg-stone-50' }}">
                            {{ $theme['name'] }}
                            @if ($activeTheme === $theme['slug'])
                                <span class="rounded-full bg-teal-100 px-2 py-0.5 text-xs text-teal-900">Active</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </section>

            <x-appearance.token-editor
                :selected-theme="$selectedTheme"
                :setting="$setting"
                :token-definitions="$tokenDefinitions"
                :draft-tokens="$draftTokens"
                :default-tokens="$defaultTokens"
                :radius-options="$radiusOptions"
                :shadow-options="$shadowOptions"
                :motion-options="$motionOptions"
                :can-update="$canUpdateAppearance"
                :can-reset="$canResetAppearance"
            />

            <x-appearance.preview-frame
                :preview="$preview"
                :radius-options="$radiusOptions"
                :shadow-options="$shadowOptions"
                :motion-options="$motionOptions"
                :signed-url="$signedPreviewUrl"
                :can-preview="$canPreviewAppearance"
            />

            <x-appearance.contrast-report :report="$contrastReport" />
        </div>

        <aside class="space-y-6">
            <x-appearance.theme-switch-warning :selected-theme="$selectedTheme" :active-theme="$activeTheme" />

            <x-appearance.publish-bar
                :selected-theme="$selectedTheme"
                :setting="$setting"
                :report="$contrastReport"
                :can-publish="$canPublishAppearance"
            />

            <x-appearance.palette-suggestions :suggestions="$paletteSuggestions" />

            <x-appearance.theme-default-card
                :selected-theme="$selectedTheme"
                :active-theme="$activeTheme"
                :default-tokens="$defaultTokens"
                :css-variables="$cssVariables"
            />

            <x-appearance.reset-warning
                :selected-theme="$selectedTheme"
                :can-reset="$canResetAppearance"
            />

            <x-appearance.version-history
                :versions="$versions"
                :selected-theme="$selectedTheme"
                :can-rollback="$canRollbackAppearance"
            />

            <x-admin.card title="Scope Guard" description="Appearance Studio stores safe token values per fixed public theme. Admin layout, Typography Center, theme layouts, uploads, and arbitrary CSS are untouched.">
                <ul class="space-y-3 text-sm font-semibold text-stone-700">
                    <li class="flex gap-2"><i data-lucide="check" class="mt-0.5 size-4 text-teal-700" aria-hidden="true"></i><span>Only allowlisted tokens can be saved.</span></li>
                    <li class="flex gap-2"><i data-lucide="check" class="mt-0.5 size-4 text-teal-700" aria-hidden="true"></i><span>Runtime output is CSS variables.</span></li>
                    <li class="flex gap-2"><i data-lucide="check" class="mt-0.5 size-4 text-teal-700" aria-hidden="true"></i><span>Settings remain separate for each theme.</span></li>
                </ul>
            </x-admin.card>
        </aside>
    </div>
</x-admin.layout>
