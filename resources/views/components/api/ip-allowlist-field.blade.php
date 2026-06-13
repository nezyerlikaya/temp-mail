@props(['disabled' => false])

<div>
    <label for="api-ip-allowlist" class="text-sm font-extrabold text-stone-800">IP allowlist</label>
    <textarea id="api-ip-allowlist" name="ip_allowlist_text" rows="3" @disabled($disabled) placeholder="Optional, one IP per line" aria-invalid="{{ $errors->has('ip_allowlist') || $errors->has('ip_allowlist.*') ? 'true' : 'false' }}" class="mt-2 w-full rounded-lg border border-stone-300 px-3 py-2 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-600/20 disabled:bg-stone-100">{{ old('ip_allowlist_text') }}</textarea>
    <p class="mt-1 text-xs font-bold text-stone-500">Leave empty to allow any source IP.</p>
    @error('ip_allowlist.*')<p class="mt-1 text-sm font-bold text-red-700">{{ $message }}</p>@enderror
</div>
