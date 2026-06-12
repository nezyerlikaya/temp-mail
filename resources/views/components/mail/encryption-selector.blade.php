@props(['options', 'selected' => 'ssl'])

<fieldset class="grid gap-2">
    <legend class="text-sm font-bold text-stone-700">Encryption <span class="text-red-700" aria-hidden="true">*</span></legend>
    <div class="grid grid-cols-3 gap-2" x-data="{ encryption: @js(old('encryption', $selected)) }">
        @foreach ($options as $value => $label)
            <label class="relative">
                <input type="radio" name="encryption" value="{{ $value }}" x-model="encryption" class="peer sr-only" required>
                <span class="flex min-h-11 items-center justify-center rounded-md border border-stone-300 bg-white px-3 text-sm font-extrabold text-stone-700 transition peer-checked:border-teal-700 peer-checked:bg-teal-50 peer-checked:text-teal-900 peer-focus-visible:ring-4 peer-focus-visible:ring-teal-600/20">{{ $label }}</span>
            </label>
        @endforeach
    </div>
    <p class="text-xs text-stone-500">SSL is the safest default for port 993. Use TLS only when your provider specifies STARTTLS.</p>
    @error('encryption')
        <span class="text-xs font-bold text-red-700" role="alert">{{ $message }}</span>
    @enderror
</fieldset>
