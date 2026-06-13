<section class="border border-white/15 bg-[#1c2421] p-5 shadow-[10px_10px_0_#b9f227]" aria-labelledby="mailbox-create-title">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h2 id="mailbox-create-title" class="text-xl font-extrabold text-white">{{ $translations['mailbox.create.title'] }}</h2>
            <p class="mt-2 text-sm leading-6 text-stone-300">{{ $translations['mailbox.create.description'] }}</p>
        </div>
        <span class="border border-lime-300 px-3 py-1 font-mono text-xs font-extrabold text-lime-300">{{ strtoupper($translations['home.visual.ready']) }}</span>
    </div>
    @if ($errors->any())
        <div class="mt-5 border border-red-300 bg-red-950/40 p-4 text-sm font-semibold text-red-100" role="alert">{{ $errors->first() }}</div>
    @endif
    @if (count($mailbox_creator['domains']) === 0)
        <div class="mt-5 border border-amber-300 bg-amber-950/30 p-4 text-sm font-semibold text-amber-100" role="status">{{ $translations['mailbox.domain.empty'] }}</div>
    @else
        <form method="POST" action="{{ $mailbox_creator['action'] }}" class="mt-5 space-y-4" x-data="{ submitting: false }" x-on:submit="submitting = true" x-bind:aria-busy="submitting">
            @csrf
            <div>
                <label for="domain_id_atlas" class="text-sm font-bold text-stone-200">{{ $translations['mailbox.domain.label'] }}</label>
                <select id="domain_id_atlas" name="domain_id" class="mt-2 w-full border border-white/20 bg-[#121715] px-3 py-3 text-sm font-semibold text-white focus:outline-none focus:ring-4 focus:ring-lime-300/30" required>
                    @foreach ($mailbox_creator['domains'] as $domain)
                        <option value="{{ $domain['id'] }}" @selected(old('domain_id') == $domain['id'] || $domain['is_default'])>{{ $domain['domain'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="alias_atlas" class="text-sm font-bold text-stone-200">{{ $translations['mailbox.alias.label'] }}</label>
                <input id="alias_atlas" name="alias" value="{{ old('alias') }}" placeholder="{{ $translations['mailbox.alias.placeholder'] }}" @disabled(! $mailbox_creator['custom_alias_allowed']) class="mt-2 w-full border border-white/20 bg-[#121715] px-3 py-3 text-sm font-semibold text-white placeholder:text-stone-500 focus:outline-none focus:ring-4 focus:ring-lime-300/30 disabled:opacity-50">
            </div>
            <button type="submit" x-bind:disabled="submitting" class="inline-flex min-h-12 w-full items-center justify-center bg-lime-300 px-5 py-3 text-sm font-extrabold text-stone-950 focus:outline-none focus:ring-4 focus:ring-lime-300/40 disabled:cursor-wait disabled:opacity-70">{{ $translations['mailbox.create.button'] }}</button>
        </form>
    @endif
</section>
