@props(['report'])
<div class="rounded-lg border border-stone-200 bg-stone-50 p-4">
    <p class="text-xs font-extrabold uppercase text-stone-500">Evidence readiness</p>
    <p class="mt-2 text-sm font-extrabold text-stone-950">{{ count($report->evidence_media_ids ?? []) }} Media Library references</p>
    <p class="mt-1 text-sm text-stone-600">No raw mailbox message body is stored by this workflow.</p>
</div>
