<aside class="fixed inset-y-0 left-0 z-50 flex w-[248px] -translate-x-full flex-col border-r border-stone-800 bg-[#15191f] text-white transition-transform duration-200 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0" x-bind:class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'" aria-label="Admin navigation">
    <div class="flex h-16 shrink-0 items-center border-b border-white/10 px-5">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-md focus:outline-none focus:ring-4 focus:ring-teal-300/25">
            <span class="grid size-9 place-items-center rounded-md bg-teal-300 text-sm font-black text-stone-950">TM</span>
            <span>
                <span class="block text-sm font-extrabold text-white">Temp Mail Cloud</span>
                <span class="block text-xs text-stone-400">Operations</span>
            </span>
        </a>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-5">
        <p class="px-3 text-xs font-bold uppercase text-stone-500">Workspace</p>
        <a href="{{ route('dashboard') }}" class="mt-2 flex min-h-11 items-center gap-3 rounded-md bg-white/10 px-3 py-2.5 text-sm font-bold text-white outline-none transition hover:bg-white/15 focus:ring-4 focus:ring-teal-300/25" aria-current="page">
            <i data-lucide="layout-dashboard" class="size-5 text-teal-300" aria-hidden="true"></i>
            Operations Overview
        </a>
    </nav>

    <div class="border-t border-white/10 p-4">
        <p class="text-xs leading-5 text-stone-400">Admin workspace</p>
        <p class="mt-1 text-sm font-semibold text-stone-200">Secure operations access</p>
    </div>
</aside>
