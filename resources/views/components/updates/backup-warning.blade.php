@props(['lockStatus', 'licenseReadiness', 'backupReadiness' => null])

<div class="rounded-lg border border-amber-200 bg-amber-50 p-5 text-amber-950">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h2 class="text-base font-extrabold">Backup and safety readiness</h2>
            <p class="mt-2 text-sm leading-6">A fresh backup is required before install actions. This step only checks manifests and server compatibility.</p>
        </div>
        <x-updates.status-badge :status="$lockStatus['locked'] ? 'locked' : 'warning'" />
    </div>

    <dl class="mt-4 grid gap-3 text-sm">
        @if ($backupReadiness)
            <div>
                <dt class="font-extrabold">Backup requirement</dt>
                <dd class="mt-1">{{ $backupReadiness['message'] }} Completed backups: {{ $backupReadiness['summary']['completed'] ?? 0 }}.</dd>
            </div>
        @endif
        <div>
            <dt class="font-extrabold">Update lock</dt>
            <dd class="mt-1">{{ $lockStatus['message'] }}</dd>
        </div>
        <div>
            <dt class="font-extrabold">{{ $licenseReadiness['label'] }}</dt>
            <dd class="mt-1">{{ $licenseReadiness['message'] }}</dd>
        </div>
        <div>
            <dt class="font-extrabold">Dry-run readiness</dt>
            <dd class="mt-1">Dry-run checks are prepared for the install step and are not executed in this part.</dd>
        </div>
    </dl>
</div>
