@props(['canImport', 'preview' => null])
@if ($canImport)
    <section class="rounded-lg border border-stone-200 bg-white p-5 shadow-sm" x-data="{ csv: @js(old('csv', '')) }">
        <p class="text-xs font-extrabold uppercase text-teal-800">Safe CSV import</p>
        <h2 class="mt-1 text-lg font-extrabold text-stone-950">Preview before import</h2>
        <p class="mt-1 text-sm text-stone-600">Header: entry_type,value,reason,source,status,expires_at. Maximum 100 rows per transaction.</p>
        <form method="POST" action="{{ route('admin.blocked-lists.import') }}" class="mt-4 space-y-3">
            @csrf
            <textarea name="csv" x-model="csv" rows="8" maxlength="60000" class="w-full rounded-lg border border-stone-300 px-3 py-2 font-mono text-xs focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20" placeholder="entry_type,value,reason,source,status,expires_at"></textarea>
            @error('csv')<p role="alert" class="text-sm font-semibold text-rose-700">{{ $message }}</p>@enderror
            <div class="flex flex-wrap gap-2">
                <button name="mode" value="preview" class="inline-flex min-h-10 items-center rounded-lg border border-stone-300 px-4 text-sm font-extrabold text-stone-700">Preview CSV</button>
                <button name="mode" value="import" class="inline-flex min-h-10 items-center rounded-lg bg-teal-700 px-4 text-sm font-extrabold text-white">Import reviewed rows</button>
            </div>
        </form>
        <div class="mt-4"><x-blocked-lists.import-preview :preview="$preview" /></div>
    </section>
@endif
