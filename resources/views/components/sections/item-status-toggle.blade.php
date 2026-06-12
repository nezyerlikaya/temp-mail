@props(['name' => 'status', 'status' => 'active', 'id'])

<label for="{{ $id }}" class="inline-flex cursor-pointer items-center gap-2 text-sm font-bold text-stone-700">
    <input type="hidden" name="{{ $name }}" value="inactive">
    <input id="{{ $id }}" type="checkbox" name="{{ $name }}" value="active" @checked($status === 'active') class="rounded border-stone-300 text-teal-700 focus:ring-teal-600/20">
    Active
</label>
