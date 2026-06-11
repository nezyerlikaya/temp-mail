@props(['profileUser'])

<tr class="border-b border-stone-200 last:border-0 hover:bg-stone-50/70">
    <td class="px-5 py-4 sm:px-6">
        <div class="flex items-center gap-3">
            <span class="grid size-9 shrink-0 place-items-center rounded-full bg-teal-100 text-sm font-extrabold text-teal-900" aria-hidden="true">{{ str($profileUser->name)->substr(0, 1)->upper() }}</span>
            <div class="min-w-0">
                <a href="{{ route('admin.people-identity.show', $profileUser) }}" class="block truncate text-sm font-extrabold text-stone-950 underline-offset-4 hover:text-teal-800 hover:underline focus:outline-none focus:ring-4 focus:ring-teal-700/20">{{ $profileUser->display_name ?: $profileUser->name }}</a>
                <p class="mt-0.5 truncate text-xs text-stone-500">{{ $profileUser->email }}</p>
            </div>
        </div>
    </td>
    <td class="px-4 py-4"><x-users.role-badge :role="$profileUser->role" /></td>
    <td class="px-4 py-4"><x-users.status-badge :status="$profileUser->status" /></td>
    <td class="hidden px-4 py-4 text-sm text-stone-600 lg:table-cell">{{ $profileUser->timezone }}</td>
    <td class="hidden px-4 py-4 text-sm text-stone-600 xl:table-cell">{{ $profileUser->created_at->format('M j, Y') }}</td>
    <td class="px-5 py-4 text-right sm:px-6">
        <a href="{{ route('admin.people-identity.edit', $profileUser) }}" class="inline-flex min-h-9 items-center justify-center rounded-md border border-stone-300 px-3 text-sm font-bold text-stone-700 transition hover:bg-white focus:outline-none focus:ring-4 focus:ring-teal-700/20">Edit</a>
    </td>
</tr>
