@props(['groups', 'active'])
<nav aria-label="Blocked list groups" class="flex gap-2 overflow-x-auto rounded-lg border border-stone-200 bg-white p-2 shadow-sm">
    @foreach ($groups as $key => $label)
        <a href="{{ route('admin.blocked-lists.index', ['group' => $key]) }}" class="inline-flex min-h-10 shrink-0 items-center rounded-lg px-4 text-sm font-extrabold focus:outline-none focus:ring-4 focus:ring-teal-600/20 {{ $active === $key ? 'bg-teal-700 text-white' : 'text-stone-700 hover:bg-stone-100' }}">{{ $label }}</a>
    @endforeach
</nav>
