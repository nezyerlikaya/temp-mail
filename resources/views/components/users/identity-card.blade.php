@props(['profileUser'])

<x-admin.card>
    <div class="flex items-start gap-4">
        <span class="grid size-12 shrink-0 place-items-center rounded-full bg-teal-100 text-lg font-extrabold text-teal-900" aria-hidden="true">
            {{ str($profileUser->display_name ?: $profileUser->name)->substr(0, 1)->upper() }}
        </span>
        <div class="min-w-0 flex-1">
            <p class="truncate text-base font-extrabold text-stone-950">{{ $profileUser->display_name ?: $profileUser->name }}</p>
            <p class="mt-1 truncate text-sm text-stone-600">{{ $profileUser->email }}</p>
            @if ($profileUser->username)
                <p class="mt-1 truncate text-sm font-semibold text-teal-800">{{ '@'.$profileUser->username }}</p>
            @endif
        </div>
    </div>
    <div class="mt-5 flex flex-wrap gap-2 border-t border-stone-200 pt-4">
        <x-users.status-badge :status="$profileUser->status" />
        <x-users.role-badge :role="$profileUser->role" />
        @if ($profileUser->is_admin)
            <span class="inline-flex items-center rounded-full bg-stone-900 px-2.5 py-1 text-xs font-bold text-white">Admin access</span>
        @endif
    </div>
</x-admin.card>
