<section class="border-2 border-stone-950 bg-yellow-100 p-5" aria-labelledby="mailbox-create-title">
    <h2 id="mailbox-create-title" class="text-xl font-extrabold text-stone-950">{{ $translations['mailbox.create.title'] }}</h2>
    <p class="mt-2 text-sm leading-6 text-stone-700">{{ $translations['mailbox.create.description'] }}</p>
    @if ($errors->any())<div class="mt-5 border border-red-700 bg-white p-4 text-sm font-semibold text-red-800" role="alert">{{ $errors->first() }}</div>@endif
    @if (count($mailbox_creator['domains']) === 0)
        <div class="mt-5 border border-stone-950 bg-white p-4 text-sm font-semibold" role="status">{{ $translations['mailbox.domain.empty'] }}</div>
    @else
        <form method="POST" action="{{ $mailbox_creator['action'] }}" class="mt-5 space-y-4" x-data="{ submitting: false }" x-on:submit="submitting = true" x-bind:aria-busy="submitting">
            @csrf
            <div>
                <label for="domain_id_legacy" class="text-sm font-bold text-stone-950">{{ $translations['mailbox.domain.label'] }}</label>
                <select id="domain_id_legacy" name="domain_id" class="mt-2 w-full border-2 border-stone-950 bg-white px-3 py-3 text-sm font-semibold focus:outline-none focus:ring-4 focus:ring-yellow-300" required>
                    @foreach ($mailbox_creator['domains'] as $domain)
                        <option value="{{ $domain['id'] }}" @selected(old('domain_id') == $domain['id'] || $domain['is_default'])>{{ $domain['domain'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="alias_legacy" class="text-sm font-bold text-stone-950">{{ $translations['mailbox.alias.label'] }}</label>
                <input id="alias_legacy" name="alias" value="{{ old('alias') }}" placeholder="{{ $translations['mailbox.alias.placeholder'] }}" @disabled(! $mailbox_creator['custom_alias_allowed']) class="mt-2 w-full border-2 border-stone-950 bg-white px-3 py-3 text-sm font-semibold placeholder:text-stone-500 focus:outline-none focus:ring-4 focus:ring-yellow-300 disabled:bg-stone-100">
            </div>
            <button type="submit" x-bind:disabled="submitting" class="min-h-12 w-full bg-stone-950 px-5 py-3 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-yellow-300 disabled:cursor-wait disabled:opacity-70">{{ $translations['mailbox.create.button'] }}</button>
        </form>
    @endif
</section>
