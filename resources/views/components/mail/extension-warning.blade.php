@props(['extension'])

<div role="{{ $extension['ready'] ? 'status' : 'alert' }}" class="flex items-start gap-3 rounded-lg border p-4 {{ $extension['ready'] ? 'border-emerald-200 bg-emerald-50 text-emerald-950' : 'border-amber-300 bg-amber-50 text-amber-950' }}">
    <span class="mt-0.5 grid size-8 shrink-0 place-items-center rounded-md {{ $extension['ready'] ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-800' }}">
        <i data-lucide="{{ $extension['ready'] ? 'badge-check' : 'triangle-alert' }}" class="size-4" aria-hidden="true"></i>
    </span>
    <div>
        <p class="text-sm font-extrabold">{{ $extension['ready'] ? 'IMAP runtime ready' : 'PHP IMAP extension required' }}</p>
        <p class="mt-1 text-sm leading-6">{{ $extension['message'] }}</p>
    </div>
</div>
