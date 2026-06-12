@props(['active' => false])

<a href="{{ route('admin.blog-studio.index', ['status' => $active ? 'all' : 'trashed']) }}" {{ $attributes->merge(['class' => 'inline-flex min-h-10 items-center justify-center rounded-lg border px-3 py-2 text-sm font-extrabold transition focus:outline-none focus:ring-4 focus:ring-teal-600/20 '.($active ? 'border-red-200 bg-red-50 text-red-800' : 'border-stone-300 text-stone-700 hover:bg-white')]) }}>
    {{ $active ? 'Viewing trash' : 'View trash' }}
</a>
