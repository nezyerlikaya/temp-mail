@props(['environment'])

<span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-extrabold {{ $environment === 'live' ? 'border-teal-200 bg-teal-50 text-teal-800' : 'border-sky-200 bg-sky-50 text-sky-800' }}">
    {{ str($environment)->headline() }}
</span>
