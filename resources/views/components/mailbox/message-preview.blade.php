@props(['message'])
<section class="overflow-hidden rounded-lg border border-stone-200 bg-white shadow-sm" x-data="{ mode: @js($message->plain_text_body ? 'plain' : 'html') }">
    <div class="flex flex-wrap items-start justify-between gap-3 border-b border-stone-200 px-4 py-4 sm:px-5">
        <div><h2 class="text-base font-extrabold text-stone-950">Message preview</h2><p class="mt-1 text-sm text-stone-600">Remote resources and executable content are blocked.</p></div>
        <div class="inline-flex rounded-md border border-stone-300 bg-stone-100 p-1" role="tablist" aria-label="Message format">
            <button type="button" role="tab" x-on:click="mode = 'plain'" x-bind:aria-selected="(mode === 'plain').toString()" class="min-h-9 rounded px-3 text-sm font-extrabold focus:outline-none focus:ring-4 focus:ring-teal-600/20" x-bind:class="mode === 'plain' ? 'bg-white text-stone-950 shadow-sm' : 'text-stone-600'" @disabled(blank($message->plain_text_body))>Plain text</button>
            <button type="button" role="tab" x-on:click="mode = 'html'" x-bind:aria-selected="(mode === 'html').toString()" class="min-h-9 rounded px-3 text-sm font-extrabold focus:outline-none focus:ring-4 focus:ring-teal-600/20" x-bind:class="mode === 'html' ? 'bg-white text-stone-950 shadow-sm' : 'text-stone-600'" @disabled(blank($message->sanitized_html_body))>Safe HTML</button>
        </div>
    </div>
    <div class="p-4 sm:p-5">
        <pre x-show="mode === 'plain'" class="min-h-64 whitespace-pre-wrap break-words font-sans text-sm leading-6 text-stone-800">{{ $message->plain_text_body ?: 'No plain-text body is available.' }}</pre>
        <iframe x-show="mode === 'html'" title="Sanitized email HTML preview" sandbox="" referrerpolicy="no-referrer" srcdoc="{{ $message->sanitized_html_body ?: '<p>No HTML body is available.</p>' }}" class="min-h-80 w-full rounded-md border border-stone-200 bg-white"></iframe>
    </div>
</section>
