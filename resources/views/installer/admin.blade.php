<x-installer.layout step="3" title="Admin Account" subtitle="Create the first operator account. The installer locks only after migrations and this administrator are both created.">
    @if (session('status'))
        <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-900" role="status">{{ session('status') }}</div>
    @endif

    <x-error-summary />

    <form method="POST" action="{{ route('install.admin.store') }}" x-data="{ submitting: false, password: '', confirmation: '', showPassword: false, showConfirmation: false }" x-on:submit="if (submitting) { $event.preventDefault(); return; } submitting = true" x-bind:aria-busy="submitting.toString()" x-bind:class="{ 'pointer-events-none opacity-70': submitting }" novalidate>
        @csrf

        <div class="grid gap-5">
            <x-form.input name="name" label="Name" autocomplete="name" />
            <x-form.input name="email" label="Email" type="email" autocomplete="email" inputmode="email" />

            <div>
                <label for="password" class="block text-sm font-bold text-stone-900">Password</label>
                <div class="relative mt-2">
                    <input id="password" name="password" x-bind:type="showPassword ? 'text' : 'password'" x-model="password" autocomplete="new-password" class="block w-full rounded-lg border border-stone-300 bg-white px-4 py-3 pr-28 text-base text-stone-950 shadow-sm outline-none transition focus:border-teal-700 focus:ring-4 focus:ring-teal-700/15" aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}" aria-describedby="password-help {{ $errors->has('password') ? 'password-error' : '' }}">
                    <button type="button" class="absolute right-2 top-1/2 -translate-y-1/2 rounded-md px-3 py-2 text-sm font-bold text-teal-800 transition hover:bg-teal-50 focus:outline-none focus:ring-4 focus:ring-teal-700/20" x-on:click="showPassword = ! showPassword" x-bind:aria-pressed="showPassword.toString()" x-bind:aria-label="showPassword ? 'Hide admin password' : 'Show admin password'">
                        <span x-text="showPassword ? 'Hide' : 'Show'"></span>
                    </button>
                </div>
                <div id="password-help" class="mt-3 grid gap-2 text-sm sm:grid-cols-3">
                    <p x-bind:class="password.length >= 8 ? 'text-emerald-700' : 'text-stone-600'">8+ characters</p>
                    <p x-bind:class="/[A-Za-z]/.test(password) && /[0-9]/.test(password) ? 'text-emerald-700' : 'text-stone-600'">Letters and numbers</p>
                    <p x-bind:class="/[^A-Za-z0-9]/.test(password) ? 'text-emerald-700' : 'text-stone-600'">Symbol included</p>
                </div>
                @error('password')
                    <p id="password-error" class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-bold text-stone-900">Confirm password</label>
                <div class="relative mt-2">
                    <input id="password_confirmation" name="password_confirmation" x-bind:type="showConfirmation ? 'text' : 'password'" x-model="confirmation" autocomplete="new-password" class="block w-full rounded-lg border border-stone-300 bg-white px-4 py-3 pr-28 text-base text-stone-950 shadow-sm outline-none transition focus:border-teal-700 focus:ring-4 focus:ring-teal-700/15">
                    <button type="button" class="absolute right-2 top-1/2 -translate-y-1/2 rounded-md px-3 py-2 text-sm font-bold text-teal-800 transition hover:bg-teal-50 focus:outline-none focus:ring-4 focus:ring-teal-700/20" x-on:click="showConfirmation = ! showConfirmation" x-bind:aria-pressed="showConfirmation.toString()" x-bind:aria-label="showConfirmation ? 'Hide password confirmation' : 'Show password confirmation'">
                        <span x-text="showConfirmation ? 'Hide' : 'Show'"></span>
                    </button>
                </div>
                <p class="mt-2 text-sm" x-bind:class="password && confirmation && password === confirmation ? 'text-emerald-700' : 'text-stone-600'" x-text="password && confirmation && password === confirmation ? 'Passwords match' : 'Passwords must match'"></p>
            </div>
        </div>

        <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('install.database') }}" class="inline-flex items-center justify-center rounded-lg border border-stone-300 px-5 py-3 text-sm font-bold text-stone-800 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-stone-300">Back</a>
            <button type="submit" x-bind:disabled="submitting" class="inline-flex items-center justify-center rounded-lg bg-teal-700 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-teal-900/10 transition hover:bg-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-700/25 disabled:cursor-wait disabled:bg-teal-900">
                <span x-show="! submitting">Run migrations and lock</span>
                <span x-cloak x-show="submitting">Finishing setup...</span>
            </button>
        </div>
    </form>
</x-installer.layout>
