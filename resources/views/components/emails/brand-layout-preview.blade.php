@props(['layout'])

<x-admin.card title="Brand layout" description="Header/footer structure is locked; template body remains editable.">
    <div class="space-y-3">
        <div class="rounded-lg border border-stone-200 bg-white p-3">
            <p class="text-sm font-extrabold text-stone-950">{{ $layout['brand_name'] }}</p>
            <p class="mt-1 text-xs font-bold text-stone-500">Standard header</p>
        </div>
        <div class="rounded-lg border border-stone-200 bg-stone-50 p-3 text-sm text-stone-700">Editable template body renders here.</div>
        <div class="rounded-lg border border-stone-200 bg-white p-3">
            <p class="text-xs font-bold text-stone-500">Footer support: {{ $layout['support_email'] }}</p>
        </div>
        <div class="grid gap-2 text-xs font-bold text-stone-600">
            <p>Brand logo readiness: {{ $layout['brand_logo_ready'] ? 'Ready' : 'Pending' }}</p>
            <p>Support links: {{ $layout['support_links_ready'] ? 'Ready' : 'Needs setup' }}</p>
            <p>Legal links: {{ $layout['legal_links_ready'] ? 'Ready' : 'Pending' }}</p>
        </div>
    </div>
</x-admin.card>
