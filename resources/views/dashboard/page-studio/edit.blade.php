<x-admin.layout :title="$page->title" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Page Studio"
        :title="$page->title"
        description="Update foundation metadata for this language-specific page record."
    >
        <x-slot:actions>
            <x-pages.status-badge :status="$page->status" />
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-6">{{ session('status') }}</x-admin.alert>
    @endif

    <x-error-summary />

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        <main class="min-w-0">
            <x-admin.card title="Page foundation" description="Keep the structural metadata ready for later editor, preview, SEO, and lifecycle parts.">
                <form
                    method="POST"
                    action="{{ route('admin.page-studio.update', $page) }}"
                    class="space-y-5"
                    x-data="{ submitting: false }"
                    x-on:submit="if (submitting) { $event.preventDefault(); return } submitting = true"
                    x-bind:aria-busy="submitting"
                    x-bind:class="{ 'pointer-events-none opacity-70': submitting }"
                >
                    @csrf
                    @method('PUT')

                    @include('dashboard.page-studio.partials.form-fields')

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <a href="{{ route('admin.page-studio.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-stone-300 px-4 py-2 text-sm font-extrabold text-stone-700 focus:outline-none focus:ring-4 focus:ring-stone-400/20">Back to pages</a>
                        <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-teal-700 px-5 py-3 text-sm font-extrabold text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-600/25">
                            <span x-show="! submitting">Save foundation</span>
                            <span x-cloak x-show="submitting">Saving...</span>
                        </button>
                    </div>
                </form>
            </x-admin.card>
        </main>

        <aside class="min-w-0 space-y-6">
            <x-admin.card title="Record overview" description="Prepared integration points for later Page Studio parts.">
                <dl class="space-y-4 text-sm">
                    <div>
                        <dt class="text-xs font-bold uppercase text-stone-500">Language</dt>
                        <dd class="mt-1"><x-pages.language-badge :locale="$page->locale" /></dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase text-stone-500">Slug</dt>
                        <dd class="mt-1 break-all font-mono text-xs text-stone-600">/{{ $page->slug }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase text-stone-500">Author</dt>
                        <dd class="mt-1 font-extrabold text-stone-950">{{ $page->author?->name ?? 'System' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase text-stone-500">Featured media</dt>
                        <dd class="mt-1 font-extrabold text-stone-950">{{ $page->featured_media_id ? '#'.$page->featured_media_id : 'Not selected' }}</dd>
                    </div>
                </dl>
            </x-admin.card>

            <x-admin.card title="Deferred workflow" description="These controls are intentionally prepared, not fully implemented here.">
                <div class="space-y-3 text-sm text-stone-600">
                    <p><span class="font-extrabold text-stone-950">Preview:</span> signed preview routes arrive later.</p>
                    <p><span class="font-extrabold text-stone-950">SEO:</span> global metadata stays in SEO Growth Center.</p>
                    <p><span class="font-extrabold text-stone-950">Trash:</span> lifecycle actions arrive in the next Page Studio part.</p>
                </div>
            </x-admin.card>
        </aside>
    </div>
</x-admin.layout>
