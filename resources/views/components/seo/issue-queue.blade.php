@props(['issues'])

<x-admin.card title="Issue queue" description="Prioritized SEO fixes. Checks stay lightweight and local to stored records.">
    @if ($issues->count() > 0)
        <div class="-m-4">
            @foreach ($issues as $issue)
                <x-seo.issue-row :issue="$issue" />
            @endforeach
        </div>
    @else
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
            <p class="text-sm font-extrabold text-emerald-800">No issues match these filters.</p>
            <p class="mt-1 text-sm text-emerald-700">Diagnostics are ready without running a heavy crawler.</p>
        </div>
    @endif
</x-admin.card>
