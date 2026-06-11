@props(['check', 'backupReadiness', 'protectedPaths', 'canInstall'])

<div class="rounded-lg border border-stone-200 bg-white shadow-sm">
    <div class="border-b border-stone-200 px-5 py-4">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-base font-extrabold text-stone-950">Dry-run install summary</h2>
                <p class="mt-1 text-sm text-stone-600">Review the package facts before starting an automatic update.</p>
            </div>
            <x-updates.status-badge :status="$check?->status ?? 'pending'" />
        </div>
    </div>

    <div class="space-y-5 p-5">
        <dl class="grid gap-4 text-sm sm:grid-cols-2">
            <div>
                <dt class="font-bold text-stone-500">Version from</dt>
                <dd class="mt-1 font-extrabold text-stone-950">{{ $check?->current_version ?? 'Not checked' }}</dd>
            </div>
            <div>
                <dt class="font-bold text-stone-500">Version to</dt>
                <dd class="mt-1 font-extrabold text-stone-950">{{ $check?->latest_version ?? 'Not checked' }}</dd>
            </div>
            <div>
                <dt class="font-bold text-stone-500">Backup requirement</dt>
                <dd class="mt-1 text-stone-700">{{ $backupReadiness['required'] ? 'Required before install' : 'Optional' }}</dd>
            </div>
            <div>
                <dt class="font-bold text-stone-500">Migration requirement</dt>
                <dd class="mt-1 text-stone-700">{{ ($check?->manifest['requires_migrations'] ?? true) ? 'Run with --force' : 'No migration flag in manifest' }}</dd>
            </div>
        </dl>

        <div>
            <p class="text-sm font-extrabold text-stone-950">Protected paths</p>
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach ($protectedPaths as $path)
                    <span class="rounded-full border border-stone-200 bg-stone-50 px-2.5 py-1 font-mono text-xs text-stone-700">{{ $path }}</span>
                @endforeach
            </div>
        </div>

        <form
            method="POST"
            action="{{ route('admin.update-center.install') }}"
            x-data="{ submitting: false }"
            x-on:submit="if (submitting) { $event.preventDefault() } else { submitting = true }"
            x-bind:aria-busy="submitting"
            x-bind:class="{ 'pointer-events-none opacity-70': submitting }"
            class="space-y-4"
        >
            @csrf
            <label class="flex gap-3 rounded-lg border border-stone-200 bg-stone-50 p-4 text-sm text-stone-700">
                <input type="checkbox" name="confirm_backup" value="1" class="mt-1 h-4 w-4 rounded border-stone-300 text-teal-700 focus:ring-4 focus:ring-teal-600/20" @error('confirm_backup') aria-invalid="true" aria-describedby="confirm-backup-error" @enderror>
                <span>I confirm a recoverable backup is available before installing this update.</span>
            </label>
            @error('confirm_backup')
                <p id="confirm-backup-error" class="text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
            @enderror

            <label class="flex gap-3 rounded-lg border border-stone-200 bg-stone-50 p-4 text-sm text-stone-700">
                <input type="checkbox" name="confirm_protected_paths" value="1" class="mt-1 h-4 w-4 rounded border-stone-300 text-teal-700 focus:ring-4 focus:ring-teal-600/20" @error('confirm_protected_paths') aria-invalid="true" aria-describedby="confirm-paths-error" @enderror>
                <span>I understand protected paths cannot be overwritten by update packages.</span>
            </label>
            @error('confirm_protected_paths')
                <p id="confirm-paths-error" class="text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
            @enderror

            <div>
                <label for="maintenance_message" class="text-sm font-extrabold text-stone-950">Maintenance message</label>
                <input id="maintenance_message" name="maintenance_message" value="{{ old('maintenance_message') }}" maxlength="160" class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @error('maintenance_message') aria-invalid="true" aria-describedby="maintenance-message-error" @enderror>
                @error('maintenance_message')
                    <p id="maintenance-message-error" class="mt-2 text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" x-bind:disabled="submitting || {{ $canInstall ? 'false' : 'true' }}" class="inline-flex w-full items-center justify-center rounded-lg bg-teal-700 px-4 py-3 text-sm font-extrabold text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-600/25 disabled:cursor-not-allowed disabled:opacity-70">
                <span x-show="! submitting">Install verified update</span>
                <span x-cloak x-show="submitting">Installing safely...</span>
            </button>
        </form>
    </div>
</div>
