@props(['canCreate', 'summary'])

<x-admin.card title="Manual Backup" description="Create a database, uploads, or combined backup. Restore is intentionally not part of MVP.">
    @if ($canCreate)
        <form method="POST" action="{{ route('admin.backups-health.store') }}" class="space-y-4" x-data="{ submitting: false }" x-on:submit="submitting = true" novalidate>
            @csrf
            <div>
                <label for="type" class="block text-sm font-bold text-stone-900">Backup type</label>
                <select id="type" name="type" required aria-invalid="{{ $errors->has('type') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('type') ? 'type-error' : 'type-help' }}" class="mt-2 block min-h-12 w-full rounded-lg border border-stone-300 bg-white px-3 text-base text-stone-950 focus:border-teal-700 focus:ring-4 focus:ring-teal-700/15">
                    <option value="database" @selected(old('type') === 'database')>Database snapshot</option>
                    <option value="storage" @selected(old('type') === 'storage')>Storage uploads</option>
                    <option value="full" @selected(old('type', 'full') === 'full')>Database + storage + config</option>
                </select>
                <p id="type-help" class="mt-2 text-sm text-stone-600">Config snapshot excludes raw .env secrets.</p>
                @error('type')
                    <p id="type-error" class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" x-bind:disabled="submitting" class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-md bg-stone-950 px-4 text-sm font-extrabold text-white transition hover:bg-stone-800 focus:outline-none focus:ring-4 focus:ring-stone-950/20 disabled:cursor-not-allowed disabled:opacity-70">
                <i data-lucide="database-backup" class="size-4" aria-hidden="true"></i>
                <span x-show="! submitting">Create backup</span>
                <span x-cloak x-show="submitting">Creating...</span>
            </button>
        </form>
    @else
        <div class="rounded-md border border-stone-200 bg-stone-50 p-4 text-sm leading-6 text-stone-600">
            Backup creation requires administrator authorization.
        </div>
    @endif

    <div class="mt-5 rounded-md border border-stone-200 bg-stone-50 p-4 text-sm leading-6 text-stone-600">
        Recommended retention readiness: keep the latest {{ $summary['recommended_keep'] }} backups. Pre-update backup shortcut is ready for Update Center.
    </div>
</x-admin.card>
