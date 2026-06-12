@props(['target', 'canUpdate' => false])

<article class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="truncate text-sm font-extrabold text-stone-950">{{ $target['label'] }}</p>
            <p class="mt-1 text-xs font-bold text-stone-500">{{ str($target['target_type'])->headline() }} · {{ $target['locale']->locale }}</p>
        </div>
        <x-seo.status-badge status="draft" />
    </div>
    <p class="mt-3 line-clamp-2 min-h-10 text-sm leading-5 text-stone-600">{{ $target['description'] }}</p>
    <p class="mt-3 truncate text-xs font-bold text-stone-500">{{ $target['canonical_path'] }}</p>

    <form method="POST" action="{{ route('admin.seo-growth-center.records.ensure') }}" class="mt-4">
        @csrf
        <input type="hidden" name="locale_id" value="{{ $target['locale_id'] }}">
        <input type="hidden" name="target_type" value="{{ $target['target_type'] }}">
        <input type="hidden" name="target_key" value="{{ $target['target_key'] }}">
        <button type="submit" class="inline-flex min-h-10 w-full items-center justify-center rounded-lg border border-stone-300 px-3 text-sm font-extrabold text-stone-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:cursor-not-allowed disabled:opacity-60" @disabled(! $canUpdate)>
            Prepare record
        </button>
    </form>
</article>
