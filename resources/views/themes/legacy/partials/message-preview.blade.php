<section class="border-2 border-stone-950 bg-white p-5" aria-labelledby="message-preview-title">
    <h2 id="message-preview-title" class="text-lg font-extrabold text-stone-950">{{ $translations['mailbox.preview.title'] }}</h2>
    @if ($mailbox['selected_message'])
        <article class="mt-4"><p class="text-sm font-bold text-stone-500">{{ $mailbox['selected_message']['sender'] }} · {{ $mailbox['selected_message']['received_at'] }}</p><h3 class="mt-2 text-xl font-extrabold text-stone-950">{{ $mailbox['selected_message']['subject'] }}</h3><div class="mt-4 text-sm leading-7 text-stone-700">@if ($mailbox['selected_message']['body_html']){!! $mailbox['selected_message']['body_html'] !!}@else<p>{{ $mailbox['selected_message']['body_text'] }}</p>@endif</div></article>
    @else
        <p class="mt-4 text-sm text-stone-600">{{ $translations['mailbox.preview.empty'] }}</p>
    @endif
</section>
