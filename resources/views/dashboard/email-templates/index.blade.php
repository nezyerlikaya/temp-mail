<x-admin.layout title="Email Templates" :user="$adminUser">
    <x-admin.page-header
        eyebrow="System"
        title="Email Templates"
        description="Language-specific system email templates with safe placeholders and sanitized HTML bodies."
    >
        <x-slot:actions>
            @if ($canCreateTemplate)
                <a href="{{ route('admin.email-templates.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-stone-950 px-4 text-sm font-extrabold text-white shadow-sm focus:outline-none focus:ring-4 focus:ring-teal-600/20">Create template</a>
            @endif
            <x-admin.status-badge status="System" />
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif

    <x-emails.validation-summary />

    <section class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Email template summary">
        <x-seo.metric-card label="Expected" :value="$summary['expected']" description="Language and template key pairs" />
        <x-seo.metric-card label="Records" :value="$summary['records']" description="Created templates" />
        <x-seo.metric-card label="Active" :value="$summary['active']" description="Ready for system use" />
        <x-seo.metric-card label="Missing" :value="$summary['missing']" description="Template gaps" />
    </section>

    <div class="mb-6">
        <x-emails.readiness-summary :readiness="$readiness" />
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        <main class="min-w-0 space-y-6">
            <x-emails.template-filter-bar :filters="$filters" :locales="$locales" :template-keys="$templateKeys" :statuses="$statuses" />

            @if ($templates->count() > 0)
                <div class="grid gap-4 lg:grid-cols-2">
                    @foreach ($templates as $template)
                        <x-emails.template-card :template="$template" :template-keys="$templateKeys" :can-update="$canUpdateTemplate" />
                    @endforeach
                </div>
                <x-admin.pagination :paginator="$templates" />
            @else
                <x-emails.empty-state :can-create="$canCreateTemplate" />
            @endif
        </main>

        <aside class="min-w-0 space-y-6">
            <x-emails.language-status :locales="$locales" :missing-queue="$missingQueue" />
            <x-emails.missing-template-list :missing="$missingQueue" />

            <x-admin.card title="Template groups" description="Initial system notification coverage.">
                <div class="space-y-2">
                    @foreach ($templateKeys as $key => $label)
                        <div class="flex items-center justify-between rounded-lg border border-stone-200 px-3 py-2">
                            <span class="text-sm font-bold text-stone-800">{{ $label }}</span>
                            <span class="text-xs font-extrabold text-stone-500">{{ str($key)->replace('_', ' ')->headline() }}</span>
                        </div>
                    @endforeach
                </div>
            </x-admin.card>

            <x-admin.card title="Safety rules" description="System email templates are content records, not executable code.">
                <div class="space-y-3 text-sm text-stone-700">
                    <p>Blade and PHP execution is rejected.</p>
                    <p>Only allowlisted variables can be saved.</p>
                    <p>Active critical templates must include required variables.</p>
                    <p>HTML is sanitized before storage.</p>
                </div>
            </x-admin.card>
        </aside>
    </div>
</x-admin.layout>
