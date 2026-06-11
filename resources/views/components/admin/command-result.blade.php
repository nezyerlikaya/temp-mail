<a
    x-bind:id="'command-result-' + command.id"
    x-bind:href="command.url"
    class="group flex min-h-16 items-center gap-3 rounded-md px-3 py-2.5 outline-none transition"
    x-bind:class="isActive(command) ? 'bg-teal-50 text-stone-950 ring-1 ring-inset ring-teal-200' : 'text-stone-700 hover:bg-stone-50'"
    x-bind:aria-selected="isActive(command).toString()"
    role="option"
    x-on:mouseenter="setActive(command)"
    x-on:click="remember(command)"
>
    <span class="grid size-9 shrink-0 place-items-center rounded-md border border-stone-200 bg-white text-stone-500 shadow-sm" aria-hidden="true">
        <i x-bind:data-lucide="command.icon" class="size-[18px]"></i>
    </span>
    <span class="min-w-0 flex-1">
        <span class="block truncate text-sm font-bold" x-text="command.title"></span>
        <span class="mt-0.5 block truncate text-xs text-stone-500" x-text="command.description"></span>
    </span>
    <span x-show="command.danger" class="shrink-0 rounded-full bg-amber-100 px-2 py-1 text-[11px] font-bold text-amber-900">Review</span>
    <i data-lucide="corner-down-left" class="size-4 shrink-0 text-stone-300 group-hover:text-stone-500" aria-hidden="true"></i>
</a>
