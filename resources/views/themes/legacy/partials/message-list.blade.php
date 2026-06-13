<section class="border-2 border-stone-950 bg-white p-5" aria-labelledby="messages-title">
    <h2 id="messages-title" class="text-lg font-extrabold text-stone-950">{{ $translations['mailbox.messages.title'] }}</h2>
    @if (count($mailbox['messages']) === 0)
        <div class="mt-4 border border-dashed border-stone-500 p-6"><p class="font-extrabold text-stone-950">{{ $translations['mailbox.empty.title'] }}</p><p class="mt-2 text-sm text-stone-600">{{ $translations['mailbox.empty.body'] }}</p></div>
    @else
        <div class="mt-4 divide-y divide-stone-300">@foreach ($mailbox['messages'] as $message)<a href="{{ $message['url'] }}" class="block py-4 underline-offset-4 hover:underline focus:outline-none focus:ring-4 focus:ring-yellow-300"><span class="block text-sm font-extrabold text-stone-950">{{ $message['subject'] }}</span><span class="mt-1 block text-sm text-stone-600">{{ $message['sender'] }} · {{ $message['received_at'] }}</span>@if ($message['preview'])<span class="mt-1 block text-sm text-stone-500">{{ $message['preview'] }}</span>@endif</a>@endforeach</div>
    @endif
</section>
