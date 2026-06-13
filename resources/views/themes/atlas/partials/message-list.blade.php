<section class="border border-white/15 bg-[#1c2421] p-5" aria-labelledby="messages-title">
    <h2 id="messages-title" class="text-lg font-extrabold text-white">{{ $translations['mailbox.messages.title'] }}</h2>
    @if (count($mailbox['messages']) === 0)
        <div class="mt-4 border border-dashed border-white/20 bg-white/5 p-6"><p class="font-extrabold text-white">{{ $translations['mailbox.empty.title'] }}</p><p class="mt-2 text-sm text-stone-300">{{ $translations['mailbox.empty.body'] }}</p></div>
    @else
        <div class="mt-4 divide-y divide-white/10">@foreach ($mailbox['messages'] as $message)<a href="{{ $message['url'] }}" class="block py-4 focus:outline-none focus:ring-4 focus:ring-lime-300/30"><span class="block text-sm font-extrabold text-white">{{ $message['subject'] }}</span><span class="mt-1 block text-sm text-stone-300">{{ $message['sender'] }} · {{ $message['received_at'] }}</span>@if ($message['preview'])<span class="mt-1 block text-sm text-stone-400">{{ $message['preview'] }}</span>@endif</a>@endforeach</div>
    @endif
</section>
