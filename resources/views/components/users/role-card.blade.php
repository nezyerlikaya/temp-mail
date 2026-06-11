@props(['summary'])

<article class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
    <div class="flex items-start justify-between gap-4">
        <div>
            <x-users.role-badge :role="$summary['role']->value" />
            <p class="mt-3 text-sm leading-6 text-stone-600">{{ $summary['role']->description() }}</p>
        </div>
        <span class="grid size-10 shrink-0 place-items-center rounded-md bg-stone-100 text-sm font-extrabold text-stone-800" aria-label="{{ $summary['count'] }} users">
            {{ $summary['count'] }}
        </span>
    </div>
    <dl class="mt-5 grid grid-cols-2 gap-3 border-t border-stone-200 pt-4 text-sm">
        <div>
            <dt class="text-stone-500">Admin access</dt>
            <dd class="mt-1 font-bold text-stone-950">{{ $summary['role']->hasAdminAccess() ? 'Allowed' : 'Blocked' }}</dd>
        </div>
        <div>
            <dt class="text-stone-500">Abilities</dt>
            <dd class="mt-1 font-bold text-stone-950">{{ $summary['permissions'] }}</dd>
        </div>
    </dl>
</article>
