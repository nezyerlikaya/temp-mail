@props(['plan', 'canUpdate' => false])
<label class="grid gap-2 text-sm font-extrabold text-stone-950">
    Currency
    <input
        name="currency"
        value="{{ old('currency', $plan->currency) }}"
        maxlength="3"
        autocomplete="off"
        @disabled(! $canUpdate)
        class="min-h-11 rounded-md border border-stone-300 px-3 uppercase focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:bg-stone-100"
        @error('currency') aria-invalid="true" aria-describedby="currency-error-{{ $plan->id }}" @enderror
    >
    @error('currency')<span id="currency-error-{{ $plan->id }}" class="text-sm font-bold text-red-700" role="alert">{{ $message }}</span>@enderror
</label>
