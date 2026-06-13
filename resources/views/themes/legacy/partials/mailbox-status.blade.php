<section class="border-2 border-stone-950 bg-white p-5" aria-labelledby="mailbox-status-title">
    <p id="mailbox-status-title" class="text-sm font-extrabold text-teal-800">{{ $mailbox['expired'] ? $translations['mailbox.status.expired'] : $translations['mailbox.status.active'] }}</p>
    <h1 class="mt-2 break-all text-2xl font-extrabold text-stone-950">{{ $mailbox['address'] }}</h1>
    <p class="mt-2 text-sm font-semibold text-stone-700">{{ $translations['mailbox.expires.label'] }}: {{ $mailbox['expires_label'] }}</p>
    <form method="POST" action="{{ $mailbox['refresh_action'] }}" class="mt-4">
        @csrf
        <input type="hidden" name="access_token" value="{{ $mailbox['access_token'] }}">
        <input type="hidden" name="return_to" value="{{ $mailbox['current_url'] }}">
        <button class="min-h-11 bg-stone-950 px-4 py-2 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-yellow-300">{{ $translations['mailbox.refresh.label'] }}</button>
    </form>
    @if (session('status'))<p class="mt-4 text-sm font-bold text-teal-800" role="status">{{ session('status') }}</p>@endif
</section>
