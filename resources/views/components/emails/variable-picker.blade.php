@props(['variables'])

<x-admin.card title="Safe variables" description="Only these placeholders can be saved. Values are escaped during rendering.">
    <div class="flex flex-wrap gap-2">
        @foreach ($variables as $key => $label)
            <button type="button" x-on:click="navigator.clipboard?.writeText('{{ '{{ '.$key.' }}' }}')" class="rounded-md border border-stone-200 bg-stone-50 px-2 py-1 text-xs font-extrabold text-stone-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20" title="{{ $label }}">
                {{ '{{ '.$key.' }}' }}
            </button>
        @endforeach
    </div>
</x-admin.card>
