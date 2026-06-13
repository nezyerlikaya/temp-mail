<x-admin.layout title="Theme Launch Center" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Brand"
        title="Theme Launch Center"
        description="Activate one fixed public theme while keeping the admin panel stable and independent."
    />

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif

    <x-error-summary />

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="space-y-6">
            <x-themes.registry-summary :count="$registeredThemeCount" />

            <div class="grid gap-4 lg:grid-cols-3">
                @foreach ($themes as $theme)
                    <x-themes.theme-card
                        :theme="$theme"
                        :can-activate="$canActivateThemes"
                        :activation-locked="$lockStatus['locked']"
                    />
                @endforeach
            </div>
        </div>

        <aside class="space-y-6">
            <x-themes.activation-lock :status="$lockStatus" />
            <x-themes.rollback-readiness :readiness="$rollbackReadiness" />

            <x-admin.card title="Scope Guard" description="Themes are fixed public renderers. Admin chrome, uploads, deletion, arbitrary CSS, Appearance Studio, and Typography Center stay outside this step.">
                <ul class="space-y-3 text-sm font-semibold text-stone-700">
                    <li class="flex gap-2"><i data-lucide="check" class="mt-0.5 size-4 text-teal-700" aria-hidden="true"></i><span>Exactly Horizon, Atlas, and Legacy are registered.</span></li>
                    <li class="flex gap-2"><i data-lucide="check" class="mt-0.5 size-4 text-teal-700" aria-hidden="true"></i><span>Only one public theme can be active.</span></li>
                    <li class="flex gap-2"><i data-lucide="check" class="mt-0.5 size-4 text-teal-700" aria-hidden="true"></i><span>The active theme cannot be disabled directly.</span></li>
                </ul>
            </x-admin.card>
        </aside>
    </div>
</x-admin.layout>
