@props(['redirect'])

<div class="grid gap-3 border-b border-stone-100 p-4 last:border-b-0 md:grid-cols-[1fr_auto_auto] md:items-center">
    <div class="min-w-0">
        <p class="truncate text-sm font-extrabold text-stone-950">{{ $redirect->source_path }} → {{ $redirect->target_url }}</p>
        <p class="mt-1 text-xs font-bold text-stone-500">{{ $redirect->status_code }} · {{ $redirect->is_active ? 'Active' : 'Paused' }}</p>
    </div>
    <x-seo.severity-badge :severity="$redirect->is_active ? 'ready' : 'notice'" />
</div>
