@props(['count' => 0])

<a
    href="{{ route('admin.notifications.index') }}"
    class="relative grid size-10 place-items-center rounded-md border border-stone-300 text-stone-700 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
    aria-label="{{ $count > 0 ? $count.' unread notifications' : 'Notifications' }}"
>
    <i data-lucide="bell" class="size-5" aria-hidden="true"></i>
    @if ($count > 0)
        <span class="absolute -right-1 -top-1 grid min-w-5 place-items-center rounded-full bg-red-600 px-1 text-[11px] font-black text-white ring-2 ring-white">
            {{ min($count, 99) }}
        </span>
    @endif
</a>
