<x-admin.layout title="Update Center" :user="$adminUser">
    <x-admin.page-header
        eyebrow="System"
        title="Update Center"
        description="Check official release manifests, review compatibility, and prepare safely before any install action."
    >
        <x-slot:actions>
            <x-admin.status-badge status="Update Ready" />
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif
    @if (session('warning'))
        <x-admin.alert variant="warning" class="mb-6">{{ session('warning') }}</x-admin.alert>
    @endif
    @if ($latestCheck?->error_message)
        <x-admin.alert variant="danger" title="Latest update check issue" class="mb-6">{{ $latestCheck->error_message }}</x-admin.alert>
    @endif

    <x-error-summary />
    <x-updates.update-lock-warning :lock-status="$lockStatus" class="mb-6" />

    <section class="mb-6 grid gap-4 lg:grid-cols-3" aria-label="Update version summary">
        <x-updates.version-card label="Installed version" :version="$currentVersion" status="current" description="Read from application configuration." />
        <x-updates.version-card label="Latest available" :version="$latestCheck?->latest_version" :status="$latestCheck?->status ?? 'pending'" description="Shown after a manifest check." />
        <x-updates.version-card label="Last checked" :version="$latestCheck?->checked_at?->format('M j, Y H:i') ?? null" :status="$latestCheck ? 'ready' : 'pending'" description="Stored in update check history." />
    </section>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
        <main class="min-w-0 space-y-6">
            <x-admin.card title="Safe update workflow" description="Manifest, package verification, installation lock, and recovery readiness stay visible before any update action.">
                <x-updates.update-stepper :current="$latestCheck?->status === 'installed' ? 'install' : 'compatibility'" />
            </x-admin.card>

            <x-updates.compatibility-checklist :compatibility="$compatibility" />
            <x-updates.install-summary :check="$latestCheck" :backup-readiness="$backupReadiness" :protected-paths="$protectedPaths" :can-install="$canInstallUpdates" />

            <x-updates.release-notes :manifest="$latestCheck?->manifest ?? []" />

            <section aria-labelledby="history-title">
                <div class="mb-4 flex items-end justify-between gap-4">
                    <div>
                        <h2 id="history-title" class="text-lg font-extrabold text-stone-950">Update check history</h2>
                        <p class="mt-1 text-sm text-stone-600">Recent manifest checks are retained for operational review.</p>
                    </div>
                    <p class="text-sm font-bold text-stone-500">{{ $history->count() }} records</p>
                </div>

                @if ($history->count() > 0)
                    <div class="overflow-hidden rounded-lg border border-stone-200 bg-white shadow-sm">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead class="bg-stone-50 text-xs font-extrabold uppercase text-stone-500">
                                    <tr>
                                        <th scope="col" class="px-4 py-3">Channel</th>
                                        <th scope="col" class="px-4 py-3">Version</th>
                                        <th scope="col" class="px-4 py-3">Status</th>
                                        <th scope="col" class="px-4 py-3">Checked by</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($history as $check)
                                        <x-updates.history-row :check="$check" />
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="rounded-lg border border-stone-200 bg-white p-8 text-center shadow-sm">
                        <p class="text-base font-extrabold text-stone-950">No update checks yet</p>
                        <p class="mt-2 text-sm text-stone-600">Run a manifest check to populate release, verification, and compatibility details.</p>
                    </div>
                @endif
            </section>
        </main>

        <aside class="min-w-0 space-y-6">
            <x-admin.card title="Check for updates" description="Uses the configured official HTTPS endpoint.">
                <form
                    method="POST"
                    action="{{ route('admin.update-center.check') }}"
                    x-data="{ submitting: false }"
                    x-on:submit="if (submitting) { $event.preventDefault() } else { submitting = true }"
                    x-bind:aria-busy="submitting"
                    x-bind:class="{ 'pointer-events-none opacity-70': submitting }"
                    class="space-y-5"
                >
                    @csrf

                    <x-updates.channel-selector :channels="$channels" :selected="$selectedChannel" />

                    <div class="rounded-lg border border-stone-200 bg-stone-50 p-4">
                        <p class="text-sm font-extrabold text-stone-950">Configured endpoint</p>
                        <p class="mt-1 break-all font-mono text-xs text-stone-600">{{ $endpoint }}</p>
                    </div>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-lg bg-stone-950 px-4 py-3 text-sm font-extrabold text-white shadow-sm transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/25 disabled:cursor-not-allowed disabled:opacity-70"
                        x-bind:disabled="submitting || {{ $canCheckUpdates ? 'false' : 'true' }}"
                    >
                        <span x-show="! submitting">Check for updates</span>
                        <span x-cloak x-show="submitting">Checking manifest...</span>
                    </button>
                </form>
            </x-admin.card>

            <x-updates.package-verification :check="$latestCheck" />
            <x-updates.backup-warning :lock-status="$lockStatus" :license-readiness="$licenseReadiness" :backup-readiness="$backupReadiness" />
            <x-updates.manual-update-panel :manual-steps="$manualSteps" :can-upload="$canUploadManualUpdates" />
            <x-updates.rollback-readiness :readiness="$rollbackReadiness" />
            <x-updates.post-update-health :check="$latestCheck" />
        </aside>
    </div>
</x-admin.layout>
