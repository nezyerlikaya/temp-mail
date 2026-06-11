@props(['locale', 'readiness', 'canManage'])

<article class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm" dir="{{ $locale->direction }}">
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-base font-extrabold text-stone-950">{{ $locale->language_name }}</h2>
                @if ($locale->is_default)
                    <span class="rounded-full bg-stone-950 px-2.5 py-1 text-xs font-extrabold text-white">Default</span>
                @endif
            </div>
            <p class="mt-1 text-sm font-bold text-stone-600">{{ $locale->native_name }} · {{ $locale->locale }} · {{ strtoupper($locale->direction) }}</p>
            <p class="mt-1 text-sm text-stone-500">{{ $locale->region }}</p>
        </div>
        <x-localization.translation-status-badge :status="$locale->launch_status" />
    </div>

    <div class="mt-5">
        <x-localization.translation-progress :score="$readiness['score']" />
    </div>

    @if (count($readiness['missing']) > 0)
        <ul class="mt-4 space-y-1 text-sm text-stone-600">
            @foreach ($readiness['missing'] as $item)
                <li>{{ $item }}</li>
            @endforeach
        </ul>
    @endif

    <div class="mt-5 grid gap-3 sm:grid-cols-2">
        <label class="rounded-lg border border-stone-200 bg-stone-50 p-3">
            <span class="text-xs font-extrabold uppercase text-stone-500">Market readiness</span>
            <select name="locales[{{ $locale->locale }}][market_readiness]" class="mt-2 w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @disabled(! $canManage)>
                @foreach (['planned' => 'Planned', 'ready' => 'Ready', 'blocked' => 'Blocked'] as $value => $label)
                    <option value="{{ $value }}" @selected(old("locales.{$locale->locale}.market_readiness", $locale->market_readiness) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="rounded-lg border border-stone-200 bg-stone-50 p-3">
            <span class="text-xs font-extrabold uppercase text-stone-500">Launch status</span>
            <select name="locales[{{ $locale->locale }}][launch_status]" class="mt-2 w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @disabled(! $canManage)>
                @foreach (['draft' => 'Draft', 'ready' => 'Ready', 'launched' => 'Launched', 'paused' => 'Paused'] as $value => $label)
                    <option value="{{ $value }}" @selected(old("locales.{$locale->locale}.launch_status", $locale->launch_status) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
    </div>

    <div class="mt-4 grid gap-3 sm:grid-cols-[1fr_1fr_110px]">
        <label class="flex items-center gap-3 rounded-lg border border-stone-200 bg-white p-3 text-sm font-bold text-stone-700">
            <input type="checkbox" name="locales[{{ $locale->locale }}][is_active]" value="1" class="h-4 w-4 rounded border-stone-300 text-teal-700 focus:ring-4 focus:ring-teal-600/20" @checked(old("locales.{$locale->locale}.is_active", $locale->is_active)) @disabled(! $canManage)>
            Active
        </label>
        <label class="flex items-center gap-3 rounded-lg border border-stone-200 bg-white p-3 text-sm font-bold text-stone-700">
            <input type="checkbox" name="locales[{{ $locale->locale }}][is_default]" value="1" class="h-4 w-4 rounded border-stone-300 text-teal-700 focus:ring-4 focus:ring-teal-600/20" @checked(old("locales.{$locale->locale}.is_default", $locale->is_default)) @disabled(! $canManage)>
            Default
        </label>
        <label class="rounded-lg border border-stone-200 bg-white p-3">
            <span class="sr-only">Sort order</span>
            <input type="number" name="locales[{{ $locale->locale }}][sort_order]" value="{{ old("locales.{$locale->locale}.sort_order", $locale->sort_order) }}" min="1" max="999" class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" @disabled(! $canManage)>
        </label>
    </div>

    <label class="mt-4 flex items-center gap-3 rounded-lg border border-stone-200 bg-stone-50 p-3 text-sm font-bold text-stone-700" dir="ltr">
        <input type="checkbox" name="locales[]" value="{{ $locale->locale }}" form="locale-bulk-form" class="js-locale-bulk-checkbox h-4 w-4 rounded border-stone-300 text-teal-700 focus:ring-4 focus:ring-teal-600/20">
        Select for bulk action
    </label>
</article>
