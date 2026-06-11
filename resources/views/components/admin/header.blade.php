@props(['user'])

<header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-stone-200 bg-white/95 px-4 backdrop-blur sm:px-6 lg:px-8">
    <div class="flex min-w-0 items-center gap-3">
        <button type="button" class="grid size-10 shrink-0 place-items-center rounded-md border border-stone-300 text-stone-700 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-600/20 lg:hidden" x-on:click="sidebarOpen = true" aria-label="Open navigation">
            <i data-lucide="menu" class="size-5" aria-hidden="true"></i>
        </button>
        <div class="min-w-0">
            <p class="truncate text-sm font-bold text-stone-950">Operations workspace</p>
            <p class="truncate text-xs text-stone-500">System administration</p>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <div class="hidden text-right sm:block">
            <p class="max-w-48 truncate text-sm font-bold text-stone-900">{{ $user->name }}</p>
            <p class="max-w-48 truncate text-xs text-stone-500">{{ $user->email }}</p>
        </div>
        <span class="grid size-9 place-items-center rounded-full bg-teal-100 text-sm font-extrabold text-teal-900" aria-hidden="true">{{ str($user->name)->substr(0, 1)->upper() }}</span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="inline-flex min-h-10 items-center gap-2 rounded-md border border-stone-300 px-3 text-sm font-bold text-stone-700 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                <i data-lucide="log-out" class="size-4" aria-hidden="true"></i>
                <span class="hidden sm:inline">Sign out</span>
            </button>
        </form>
    </div>
</header>
