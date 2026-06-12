@props(['preview'])

<div x-data="{ mode: 'desktop' }" class="space-y-4">
    <div class="flex flex-wrap gap-2" role="tablist" aria-label="Email preview modes">
        @foreach (['desktop' => 'Desktop', 'mobile' => 'Mobile', 'plain' => 'Plain text', 'dark' => 'Dark mode'] as $mode => $label)
            <button type="button" role="tab" x-bind:aria-selected="mode === '{{ $mode }}'" x-on:click="mode = '{{ $mode }}'" x-bind:class="mode === '{{ $mode }}' ? 'bg-stone-950 text-white' : 'border border-stone-300 text-stone-800'" class="inline-flex min-h-10 items-center justify-center rounded-lg px-3 text-sm font-extrabold focus:outline-none focus:ring-4 focus:ring-teal-600/20">{{ $label }}</button>
        @endforeach
    </div>

    <div x-show="mode === 'desktop'" class="rounded-lg border border-stone-200 bg-stone-100 p-4">
        <iframe title="Desktop email preview" sandbox="" referrerpolicy="no-referrer" srcdoc="{{ $preview['desktop_html'] }}" class="h-[520px] w-full rounded-lg border border-stone-300 bg-white"></iframe>
    </div>
    <div x-cloak x-show="mode === 'mobile'" class="rounded-lg border border-stone-200 bg-stone-100 p-4">
        <iframe title="Mobile email preview" sandbox="" referrerpolicy="no-referrer" srcdoc="{{ $preview['mobile_html'] }}" class="mx-auto h-[620px] w-full max-w-[390px] rounded-[24px] border border-stone-300 bg-white"></iframe>
    </div>
    <div x-cloak x-show="mode === 'plain'" class="rounded-lg border border-stone-200 bg-white p-4">
        <pre class="whitespace-pre-wrap text-sm leading-6 text-stone-800">{{ $preview['plain_text'] }}</pre>
    </div>
    <div x-cloak x-show="mode === 'dark'" class="rounded-lg border border-stone-800 bg-stone-950 p-4 text-white">
        <p class="mb-3 text-sm font-bold text-stone-300">{{ $preview['dark_mode_note'] }}</p>
        <iframe title="Dark mode email preview" sandbox="" referrerpolicy="no-referrer" srcdoc="{{ $preview['desktop_html'] }}" class="h-[520px] w-full rounded-lg border border-stone-700 bg-white opacity-90 invert"></iframe>
    </div>
</div>
