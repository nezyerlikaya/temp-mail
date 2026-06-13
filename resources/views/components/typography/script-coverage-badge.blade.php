@props(['script'])

<span class="inline-flex items-center rounded-full bg-teal-50 px-2.5 py-1 text-xs font-extrabold text-teal-900 ring-1 ring-teal-100">
    {{ str($script)->replace('_', ' ')->headline() }}
</span>
