@props(['item'])

<a
    href="{{ route($item['route']) }}"
    class="group flex min-h-10 items-center gap-3 rounded-md px-3 py-2 text-sm font-semibold outline-none transition {{ $item['active'] ? 'bg-white/12 text-white' : 'text-stone-300 hover:bg-white/7 hover:text-white' }} focus:ring-4 focus:ring-teal-300/25"
    @if ($item['active']) aria-current="page" @endif
    x-on:click="sidebarOpen = false"
>
    <i data-lucide="{{ $item['icon'] }}" class="size-[18px] shrink-0 {{ $item['active'] ? 'text-teal-300' : 'text-stone-500 group-hover:text-stone-300' }}" aria-hidden="true"></i>
    <span class="min-w-0 flex-1 leading-5">{{ $item['label'] }}</span>
    @if ($item['badge'])
        <x-admin.sidebar-badge>{{ $item['badge'] }}</x-admin.sidebar-badge>
    @endif
</a>
