@props(['check'])

<div class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h2 class="text-base font-extrabold text-stone-950">Package verification readiness</h2>
            <p class="mt-1 text-sm leading-6 text-stone-600">Signature and checksum metadata are shown for review only. Install trust is deferred.</p>
        </div>
        <x-updates.status-badge :status="$check?->signed_manifest ? 'passed' : 'warning'" />
    </div>

    <dl class="mt-5 grid gap-4 text-sm">
        <div>
            <dt class="font-bold text-stone-500">Signed manifest</dt>
            <dd class="mt-1 font-extrabold text-stone-950">{{ $check?->signed_manifest ? 'Present' : 'Missing or unchecked' }}</dd>
        </div>
        <div>
            <dt class="font-bold text-stone-500">Checksum</dt>
            <dd class="mt-1 break-all font-mono text-xs text-stone-800">{{ $check?->checksum ?: 'Not provided' }}</dd>
        </div>
        <div>
            <dt class="font-bold text-stone-500">Signature</dt>
            <dd class="mt-1 break-all font-mono text-xs text-stone-800">{{ $check?->signature ? str($check->signature)->limit(96) : 'Not provided' }}</dd>
        </div>
    </dl>
</div>
