<x-admin.layout title="Create Page" :user="$adminUser">
    <x-admin.page-header
        eyebrow="Page Studio"
        title="Create page foundation"
        description="Create a language-specific page record before the full editor and preview workflow arrives."
    >
        <x-slot:actions>
            <a href="{{ route('admin.page-studio.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-stone-300 px-4 py-2 text-sm font-extrabold text-stone-700 transition hover:bg-white focus:outline-none focus:ring-4 focus:ring-teal-600/20">Back to pages</a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-error-summary />

    <x-admin.card title="Page foundation" description="Pages are language-specific records, not translation records.">
        <form
            method="POST"
            action="{{ route('admin.page-studio.store') }}"
            class="space-y-5"
            x-data="{ submitting: false }"
            x-on:submit="if (submitting) { $event.preventDefault(); return } submitting = true"
            x-bind:aria-busy="submitting"
            x-bind:class="{ 'pointer-events-none opacity-70': submitting }"
        >
            @csrf

            @include('dashboard.page-studio.partials.form-fields')

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.page-studio.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-stone-300 px-4 py-2 text-sm font-extrabold text-stone-700 focus:outline-none focus:ring-4 focus:ring-stone-400/20">Cancel</a>
                <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-lg bg-stone-950 px-5 py-3 text-sm font-extrabold text-white shadow-sm transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-teal-600/25">
                    <span x-show="! submitting">Create page</span>
                    <span x-cloak x-show="submitting">Creating...</span>
                </button>
            </div>
        </form>
    </x-admin.card>
</x-admin.layout>
