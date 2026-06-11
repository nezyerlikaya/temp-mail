<aside class="fixed inset-y-0 left-0 z-50 flex w-[276px] -translate-x-full flex-col border-r border-stone-800 bg-[#15191f] text-white transition-transform duration-200 lg:sticky lg:top-0 lg:h-screen lg:w-[276px] lg:translate-x-0" x-bind:class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'" aria-label="Admin navigation">
    <div class="flex h-16 shrink-0 items-center justify-between border-b border-white/10 px-4">
        <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3 rounded-md focus:outline-none focus:ring-4 focus:ring-teal-300/25">
            <span class="grid size-9 shrink-0 place-items-center rounded-md bg-teal-300 text-sm font-black text-stone-950">TM</span>
            <span class="min-w-0">
                <span class="block truncate text-sm font-extrabold text-white">{{ config('app.name') }}</span>
                <span class="block truncate text-xs text-stone-400">Operations cockpit</span>
            </span>
        </a>

        <button type="button" class="grid size-9 shrink-0 place-items-center rounded-md text-stone-300 transition hover:bg-white/10 hover:text-white focus:outline-none focus:ring-4 focus:ring-teal-300/25 lg:hidden" x-on:click="sidebarOpen = false" aria-label="Close navigation">
            <i data-lucide="x" class="size-5" aria-hidden="true"></i>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-4" aria-label="Admin modules">
        <div class="space-y-5">
            @foreach ($navigationGroups as $group)
                <x-admin.sidebar-group :label="$group['label']" :items="$group['items']" />
            @endforeach
        </div>
    </nav>

    <div class="border-t border-white/10 p-4">
        <div class="flex items-center gap-3 rounded-md bg-white/5 px-3 py-2.5">
            <span class="grid size-8 shrink-0 place-items-center rounded-full bg-teal-300/15 text-teal-200" aria-hidden="true">
                <i data-lucide="shield-check" class="size-4"></i>
            </span>
            <div class="min-w-0">
                <p class="truncate text-xs font-bold text-stone-200">Permission-aware access</p>
                <p class="truncate text-xs text-stone-500">Admin navigation secured</p>
            </div>
        </div>
    </div>
</aside>
