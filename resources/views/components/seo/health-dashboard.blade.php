@props(['diagnostics', 'canRun' => false])

<x-admin.card title="SEO diagnostics" description="Lightweight checks for metadata, canonical safety, schema, social cards, hreflang, and robots risk.">
    <div class="grid gap-3 sm:grid-cols-4">
        <div class="rounded-lg border border-stone-200 bg-stone-50 p-3">
            <p class="text-xs font-extrabold uppercase text-stone-500">Open issues</p>
            <p class="mt-2 text-2xl font-extrabold text-stone-950">{{ $diagnostics['summary']['total'] }}</p>
        </div>
        <div class="rounded-lg border border-red-200 bg-red-50 p-3">
            <p class="text-xs font-extrabold uppercase text-red-700">Critical</p>
            <p class="mt-2 text-2xl font-extrabold text-red-800">{{ $diagnostics['summary']['critical'] }}</p>
        </div>
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-3">
            <p class="text-xs font-extrabold uppercase text-amber-800">Warnings</p>
            <p class="mt-2 text-2xl font-extrabold text-amber-900">{{ $diagnostics['summary']['warning'] }}</p>
        </div>
        <div class="rounded-lg border border-sky-200 bg-sky-50 p-3">
            <p class="text-xs font-extrabold uppercase text-sky-700">Notices</p>
            <p class="mt-2 text-2xl font-extrabold text-sky-900">{{ $diagnostics['summary']['notice'] }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.seo-growth-center.diagnostics.run') }}" class="mt-4 grid gap-3 sm:grid-cols-[1fr_1fr_auto]" x-data="{ submitting: false }" x-on:submit="submitting = true" x-bind:aria-busy="submitting.toString()">
        @csrf
        <label class="text-sm font-bold text-stone-700">
            <span>Severity</span>
            <select name="severity" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                @foreach (['all' => 'All severities', 'critical' => 'Critical', 'warning' => 'Warning', 'notice' => 'Notice'] as $value => $label)
                    <option value="{{ $value }}" @selected($diagnostics['filters']['severity'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="text-sm font-bold text-stone-700">
            <span>Issue type</span>
            <select name="issue" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 bg-white px-3 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                @foreach (['all' => 'All issues', 'missing_metadata' => 'Missing metadata', 'duplicate_title' => 'Duplicate title', 'duplicate_description' => 'Duplicate description', 'missing_og_image' => 'Missing OG image', 'invalid_canonical' => 'Invalid canonical', 'noindex_risk' => 'Noindex risk', 'missing_schema' => 'Missing schema', 'slug_conflict' => 'Slug conflict'] as $value => $label)
                    <option value="{{ $value }}" @selected($diagnostics['filters']['issue'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <button type="submit" @disabled(! $canRun) x-bind:disabled="submitting || {{ $canRun ? 'false' : 'true' }}" class="self-end inline-flex min-h-11 items-center justify-center rounded-lg bg-stone-950 px-4 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:opacity-60">
            <span x-text="submitting ? 'Refreshing...' : 'Run diagnostics'"></span>
        </button>
    </form>
</x-admin.card>
