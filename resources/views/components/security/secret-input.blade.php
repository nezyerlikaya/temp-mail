@props(['name', 'label', 'masked' => null, 'canReveal' => false, 'revealUrl' => null])

<label class="grid gap-2" x-data="{ revealed: false, value: @js($masked), loading: false }">
    <span class="text-sm font-bold text-stone-700">{{ $label }}</span>
    <div class="flex gap-2">
        <input
            x-bind:type="revealed ? 'text' : 'password'"
            name="{{ $name }}"
            value=""
            placeholder="{{ $masked ?: 'Paste new value' }}"
            autocomplete="new-password"
            class="min-h-11 min-w-0 flex-1 rounded-md border border-stone-300 px-3 text-sm font-bold text-stone-800 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
        >
        @if ($canReveal && $revealUrl)
            <button
                type="button"
                class="inline-flex min-h-11 items-center rounded-md border border-stone-300 px-3 text-sm font-extrabold text-stone-700 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
                x-on:click="loading = true; fetch(@js($revealUrl), { headers: { 'Accept': 'application/json' } }).then(response => response.json()).then(data => { value = data.value || ''; revealed = true; $el.closest('label').querySelector('input').value = value; }).finally(() => loading = false)"
                x-bind:aria-pressed="revealed.toString()"
            >
                <span x-text="loading ? 'Loading' : (revealed ? 'Hide' : 'Reveal')"></span>
            </button>
        @endif
    </div>
    <span class="text-xs font-semibold text-stone-500">Existing values stay encrypted unless a new value is submitted.</span>
</label>
