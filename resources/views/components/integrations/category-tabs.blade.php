@props(['categories' => [], 'activeCategory' => 'all', 'environment' => 'sandbox'])

<nav class="flex flex-wrap gap-2" aria-label="Integration categories">
    <a href="{{ route('admin.integrations.index', ['category' => 'all', 'environment' => $environment]) }}" class="inline-flex min-h-10 items-center rounded-md border px-3 py-2 text-sm font-extrabold transition focus:outline-none focus:ring-4 focus:ring-teal-700/20 {{ $activeCategory === 'all' ? 'border-teal-700 bg-teal-50 text-teal-950' : 'border-stone-200 bg-white text-stone-700 hover:bg-stone-50' }}">All</a>
    @foreach ($categories as $key => $label)
        <a href="{{ route('admin.integrations.index', ['category' => $key, 'environment' => $environment]) }}" class="inline-flex min-h-10 items-center rounded-md border px-3 py-2 text-sm font-extrabold transition focus:outline-none focus:ring-4 focus:ring-teal-700/20 {{ $activeCategory === $key ? 'border-teal-700 bg-teal-50 text-teal-950' : 'border-stone-200 bg-white text-stone-700 hover:bg-stone-50' }}">{{ $label }}</a>
    @endforeach
</nav>
