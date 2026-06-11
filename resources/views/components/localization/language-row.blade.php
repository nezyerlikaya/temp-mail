@props(['locale'])

<tr class="border-b border-stone-200 last:border-0">
    <td class="px-4 py-3 font-extrabold text-stone-950">{{ $locale->language_name }}</td>
    <td class="px-4 py-3 text-stone-600">{{ $locale->native_name }}</td>
    <td class="px-4 py-3 font-mono text-xs text-stone-600">{{ $locale->locale }}</td>
    <td class="px-4 py-3"><x-localization.translation-status-badge :status="$locale->launch_status" /></td>
</tr>
