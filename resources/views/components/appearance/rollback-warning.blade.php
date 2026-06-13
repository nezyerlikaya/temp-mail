@props(['version', 'selectedTheme'])

<label class="mt-3 flex items-start gap-3 rounded-md border border-amber-200 bg-amber-50 p-3">
    <input type="checkbox" name="confirmation" value="1" class="mt-1 size-4 rounded border-amber-300 text-amber-700 focus:ring-4 focus:ring-amber-700/20">
    <span class="text-sm font-semibold leading-6 text-amber-950">Restore {{ str($selectedTheme)->headline() }} version {{ $version->version_number }} as a new published version.</span>
</label>
