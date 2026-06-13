@props(['disabled' => false])

<div>
    <label for="api-expires-at" class="text-sm font-extrabold text-stone-800">Expires at</label>
    <input id="api-expires-at" type="datetime-local" name="expires_at" value="{{ old('expires_at') }}" @disabled($disabled) aria-invalid="{{ $errors->has('expires_at') ? 'true' : 'false' }}" class="mt-2 min-h-11 w-full rounded-lg border border-stone-300 px-3 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:bg-stone-100">
    <p class="mt-1 text-xs font-bold text-stone-500">Optional. Expired keys fail authentication automatically.</p>
    @error('expires_at')<p class="mt-1 text-sm font-bold text-red-700">{{ $message }}</p>@enderror
</div>
