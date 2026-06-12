@props(['hasSecret' => false, 'required' => false])

<label class="grid gap-2">
    <span class="text-sm font-bold text-stone-700">
        {{ $hasSecret ? 'Replace password' : 'Provider password' }}
        @if ($required)<span class="text-red-700" aria-hidden="true">*</span>@endif
    </span>
    <input
        type="password"
        name="password"
        value=""
        @required($required)
        autocomplete="new-password"
        aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
        aria-describedby="{{ $errors->has('password') ? 'inbound-password-error' : 'inbound-password-help' }}"
        class="min-h-11 rounded-md border border-stone-300 px-3 text-sm font-bold text-stone-900 focus:border-teal-600 focus:outline-none focus:ring-4 focus:ring-teal-600/20"
    >
    <span id="inbound-password-help" class="text-xs leading-5 text-stone-500">
        {{ $hasSecret ? 'A credential is stored securely. Leave this blank to keep it unchanged.' : 'The password is encrypted before storage and is never redisplayed.' }}
    </span>
    @error('password')
        <span id="inbound-password-error" class="text-xs font-bold text-red-700" role="alert">{{ $message }}</span>
    @enderror
</label>
