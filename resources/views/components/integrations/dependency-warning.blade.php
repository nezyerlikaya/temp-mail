@props(['dependency'])

<div class="mb-4 rounded-md border border-sky-200 bg-sky-50 p-3 text-sm font-semibold text-sky-950">
    <p class="font-extrabold">{{ $dependency['owner'] }}</p>
    <p class="mt-1">{{ $dependency['message'] }}</p>
</div>
