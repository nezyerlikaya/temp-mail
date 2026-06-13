@props(['types', 'canRun', 'result' => null])
@if ($canRun)
    <section class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm">
        <p class="text-xs font-extrabold uppercase text-teal-800">Enforcement test</p>
        <h2 class="mt-1 text-lg font-extrabold text-stone-950">Check a safe sample</h2>
        <p class="mt-1 text-sm text-stone-600">Runs centralized matching without changing records or storing private content.</p>
        <form method="POST" action="{{ route('admin.blocked-lists.test') }}" class="mt-4 space-y-3" x-data="{ busy: false }" x-on:submit="if (busy) $event.preventDefault(); busy = true" :aria-busy="busy">
            @csrf
            <div><label for="test-entry-type" class="text-sm font-bold text-stone-900">Check type</label><select id="test-entry-type" name="entry_type" class="mt-2 min-h-10 w-full rounded-lg border border-stone-300 px-3 text-sm">@foreach($types as $key => $label)<option value="{{ $key }}" @selected(old('entry_type') === $key)>{{ $label }}</option>@endforeach</select></div>
            <div><label for="test-value" class="text-sm font-bold text-stone-900">Sample value</label><textarea id="test-value" name="value" rows="3" maxlength="2000" class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20">{{ old('value') }}</textarea>@error('value')<p role="alert" class="mt-1 text-sm font-semibold text-rose-700">{{ $message }}</p>@enderror</div>
            <button class="inline-flex min-h-10 items-center rounded-lg bg-stone-950 px-4 text-sm font-extrabold text-white disabled:opacity-60" x-bind:disabled="busy"><span x-show="!busy">Run test</span><span x-show="busy">Checking...</span></button>
        </form>
        <div class="mt-4"><x-blocked-lists.match-result :result="$result" /></div>
    </section>
@endif
