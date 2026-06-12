@props(['name' => 'grant_note'])
<label class="grid gap-2 text-sm font-extrabold text-stone-950">
    Grant note
    <textarea name="{{ $name }}" rows="3" maxlength="500" class="rounded-md border border-stone-300 px-3 py-2 text-sm font-bold text-stone-900 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20" placeholder="Manual grant context, support ticket, or internal approval note">{{ old($name) }}</textarea>
    <span class="text-xs font-bold text-stone-500">Notes are internal. Do not include payment card data or secrets.</span>
</label>
