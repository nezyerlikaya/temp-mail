@props(['channels', 'selected'])

<fieldset class="space-y-3" aria-describedby="channel-help @error('channel') channel-error @enderror">
    <legend class="text-sm font-extrabold text-stone-950">Update channel</legend>
    <p id="channel-help" class="text-sm text-stone-600">Choose the manifest stream to check. This does not download or install a package.</p>

    <div class="grid gap-3">
        @foreach ($channels as $value => $channel)
            <label class="flex cursor-pointer gap-3 rounded-lg border border-stone-200 bg-white p-4 transition hover:border-teal-400 has-[:checked]:border-teal-600 has-[:checked]:bg-teal-50">
                <input
                    type="radio"
                    name="channel"
                    value="{{ $value }}"
                    class="mt-1 h-4 w-4 border-stone-300 text-teal-700 focus:ring-4 focus:ring-teal-600/20"
                    @checked(old('channel', $selected) === $value)
                    @error('channel') aria-invalid="true" @enderror
                >
                <span>
                    <span class="block text-sm font-extrabold text-stone-950">{{ $channel['label'] }}</span>
                    <span class="mt-1 block text-sm leading-5 text-stone-600">{{ $channel['description'] }}</span>
                </span>
            </label>
        @endforeach
    </div>

    @error('channel')
        <p id="channel-error" class="text-sm font-bold text-red-700" role="alert">{{ $message }}</p>
    @enderror
</fieldset>
