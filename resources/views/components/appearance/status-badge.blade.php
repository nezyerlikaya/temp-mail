@props(['mode'])

<span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-extrabold ring-1 ring-inset {{ $mode === 'custom' ? 'bg-blue-50 text-blue-800 ring-blue-200' : 'bg-teal-50 text-teal-800 ring-teal-200' }}">
    <span class="size-1.5 rounded-full {{ $mode === 'custom' ? 'bg-blue-700' : 'bg-teal-700' }}" aria-hidden="true"></span>
    {{ $mode === 'custom' ? 'Custom draft' : 'Using defaults' }}
</span>
