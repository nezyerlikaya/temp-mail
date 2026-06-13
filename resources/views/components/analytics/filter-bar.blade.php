@props(['filters', 'presets', 'locales', 'domains'])

<x-admin.card title="Analytics filters" description="Use aggregate date, language, and domain filters. Custom range fields are ready for scheduled reporting workflows.">
    <form method="GET" action="{{ route('admin.product-analytics.index') }}" class="space-y-4" x-data="{ submitting: false }" x-on:submit="submitting = true">
        <x-analytics.date-range-picker :filters="$filters" :presets="$presets" />
        <div class="grid gap-3 md:grid-cols-[1fr_1fr_auto]">
            <label class="grid gap-2">
                <span class="text-sm font-extrabold text-stone-800">Language</span>
                <select name="locale_id" class="min-h-11 rounded-lg border border-stone-300 px-3 text-sm font-bold focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                    <option value="all">All languages</option>
                    @foreach($locales as $locale)
                        <option value="{{ $locale->id }}" @selected((string) $filters['locale_id'] === (string) $locale->id)>{{ $locale->locale }} · {{ $locale->language_name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="grid gap-2">
                <span class="text-sm font-extrabold text-stone-800">Domain</span>
                <select name="domain_id" class="min-h-11 rounded-lg border border-stone-300 px-3 text-sm font-bold focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">
                    <option value="all">All domains</option>
                    @foreach($domains as $domain)
                        <option value="{{ $domain->id }}" @selected((string) $filters['domain_id'] === (string) $domain->id)>{{ $domain->domain_name }}</option>
                    @endforeach
                </select>
            </label>
            <div class="flex items-end">
                <button type="submit" x-bind:disabled="submitting" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg bg-stone-950 px-4 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:opacity-60">
                    <span x-show="!submitting">Apply</span>
                    <span x-cloak x-show="submitting">Applying...</span>
                </button>
            </div>
        </div>
    </form>
</x-admin.card>
