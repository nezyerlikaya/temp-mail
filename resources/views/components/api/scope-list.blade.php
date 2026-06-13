@props(['scopes', 'disabled' => false])

<fieldset {{ $attributes }}>
    <legend class="text-sm font-extrabold text-stone-800">Scopes</legend>
    <div class="mt-2 grid gap-2 sm:grid-cols-2">
        @foreach($scopes as $scope => $label)
            <label class="flex gap-3 rounded-lg border border-stone-200 p-3">
                <input type="checkbox" name="scopes[]" value="{{ $scope }}" @checked(in_array($scope, old('scopes', []), true)) @disabled($disabled) class="mt-1 size-4 rounded border-stone-300 text-teal-700 focus:ring-4 focus:ring-teal-600/20">
                <span>
                    <span class="block text-sm font-extrabold text-stone-900">{{ $scope }}</span>
                    <span class="block text-sm leading-5 text-stone-600">{{ $label }}</span>
                </span>
            </label>
        @endforeach
    </div>
    @error('scopes')<p class="mt-1 text-sm font-bold text-red-700">{{ $message }}</p>@enderror
</fieldset>
