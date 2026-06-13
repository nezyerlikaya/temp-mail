<section class="border border-stone-200 bg-[#f4f7f6] p-5 shadow-[12px_12px_0_#d9ede7]" aria-labelledby="mailbox-create-title">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h2 id="mailbox-create-title" class="text-xl font-extrabold text-stone-950">{{ $translations['mailbox.create.title'] }}</h2>
            <p class="mt-2 text-sm leading-6 text-stone-600">{{ $translations['mailbox.create.description'] }}</p>
        </div>
        <span class="bg-emerald-700 px-3 py-1 text-xs font-extrabold text-white">{{ strtoupper($translations['home.visual.ready']) }}</span>
    </div>

    @if ($errors->any())
        <div class="mt-5 border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    @if (count($mailbox_creator['domains']) === 0)
        <div class="mt-5 border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-900" role="status">
            {{ $translations['mailbox.domain.empty'] }}
        </div>
    @else
        <form method="POST" action="{{ $mailbox_creator['action'] }}" class="mt-5 space-y-4" x-data="{ submitting: false }" x-on:submit="submitting = true" x-bind:aria-busy="submitting">
            @csrf
            <div>
                <label for="domain_id_horizon" class="text-sm font-bold text-stone-800">{{ $translations['mailbox.domain.label'] }}</label>
                <select id="domain_id_horizon" name="domain_id" class="mt-2 w-full border border-stone-300 bg-white px-3 py-3 text-sm font-semibold text-stone-950 focus:outline-none focus:ring-4 focus:ring-emerald-600/25" required>
                    @foreach ($mailbox_creator['domains'] as $domain)
                        <option value="{{ $domain['id'] }}" @selected(old('domain_id') == $domain['id'] || $domain['is_default'])>{{ $domain['domain'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="alias_horizon" class="text-sm font-bold text-stone-800">{{ $translations['mailbox.alias.label'] }}</label>
                <input id="alias_horizon" name="alias" value="{{ old('alias') }}" placeholder="{{ $translations['mailbox.alias.placeholder'] }}" @disabled(! $mailbox_creator['custom_alias_allowed']) class="mt-2 w-full border border-stone-300 bg-white px-3 py-3 text-sm font-semibold text-stone-950 placeholder:text-stone-400 focus:outline-none focus:ring-4 focus:ring-emerald-600/25 disabled:bg-stone-100 disabled:text-stone-500">
            </div>
            <button type="submit" x-bind:disabled="submitting" class="inline-flex min-h-12 w-full items-center justify-center bg-emerald-700 px-5 py-3 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-emerald-600/30 disabled:cursor-wait disabled:opacity-70">
                {{ $translations['mailbox.create.button'] }}
            </button>
        </form>
    @endif
</section>
