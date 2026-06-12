@props(['value' => 993])

<label class="grid gap-2">
    <span class="text-sm font-bold text-stone-700">Port <span class="text-red-700" aria-hidden="true">*</span></span>
    <input
        type="number"
        name="port"
        min="1"
        max="65535"
        inputmode="numeric"
        value="{{ old('port', $value) }}"
        required
        aria-invalid="{{ $errors->has('port') ? 'true' : 'false' }}"
        aria-describedby="{{ $errors->has('port') ? 'port-error' : 'port-help' }}"
        class="no-spinner min-h-11 rounded-md border border-stone-300 px-3 text-sm font-bold text-stone-900 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
    >
    <span id="port-help" class="text-xs text-stone-500">Common values: 993 for SSL, 143 for TLS or unencrypted IMAP.</span>
    @error('port')
        <span id="port-error" class="text-xs font-bold text-red-700" role="alert">{{ $message }}</span>
    @enderror
</label>
