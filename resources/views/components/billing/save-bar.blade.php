@props(['canUpdate' => false, 'label' => 'Save changes'])
<div class="mt-4 flex items-center justify-between gap-4 rounded-lg border border-stone-200 bg-stone-50 p-3">
    <p class="text-sm font-bold text-stone-600">Manual billing only. Price changes do not charge users.</p>
    <button type="submit" @disabled(! $canUpdate) class="inline-flex min-h-10 shrink-0 items-center rounded-md bg-teal-700 px-4 text-sm font-extrabold text-white hover:bg-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:bg-stone-400">{{ $label }}</button>
</div>
