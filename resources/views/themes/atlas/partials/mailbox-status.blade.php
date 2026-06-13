<section class="border border-white/15 bg-[#1c2421] p-5" aria-labelledby="mailbox-status-title">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p id="mailbox-status-title" class="font-mono text-sm font-extrabold text-lime-300">{{ $mailbox['expired'] ? $translations['mailbox.status.expired'] : $translations['mailbox.status.active'] }}</p>
            <h1 class="mt-2 break-all text-2xl font-extrabold text-white sm:text-3xl">{{ $mailbox['address'] }}</h1>
            <p class="mt-2 text-sm font-semibold text-stone-300">{{ $translations['mailbox.expires.label'] }}: {{ $mailbox['expires_label'] }}</p>
        </div>
        <form method="POST" action="{{ $mailbox['refresh_action'] }}">
            @csrf
            <input type="hidden" name="access_token" value="{{ $mailbox['access_token'] }}">
            <input type="hidden" name="return_to" value="{{ $mailbox['current_url'] }}">
            <button class="min-h-11 bg-lime-300 px-4 py-2 text-sm font-extrabold text-stone-950 focus:outline-none focus:ring-4 focus:ring-lime-300/30">{{ $translations['mailbox.refresh.label'] }}</button>
        </form>
    </div>
    @if (session('status'))<p class="mt-4 text-sm font-bold text-lime-300" role="status">{{ session('status') }}</p>@endif
</section>
