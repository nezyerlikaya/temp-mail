<x-auth.layout title="Sign in">
    <header class="mb-8">
        <p class="text-sm font-bold uppercase tracking-[0.16em] text-teal-700">Secure access</p>
        <h1 class="mt-2 text-3xl font-bold text-stone-950">Sign in</h1>
        <p class="mt-3 text-sm leading-6 text-stone-600">Manage domains, inbox routing, and abuse controls from one calm control room.</p>
    </header>

    @if (session('status'))
        <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-900" role="status">{{ session('status') }}</div>
    @endif

    <x-error-summary />

    <form method="POST" action="{{ route('login.store') }}" class="space-y-5" x-data="{ showPassword: false }" novalidate>
        @csrf

        <x-form.input name="email" label="Email" type="email" autocomplete="email" inputmode="email" />

        <div>
            <label for="password" class="block text-sm font-bold text-stone-900">Password</label>
            <div class="relative mt-2">
                <input id="password" name="password" x-bind:type="showPassword ? 'text' : 'password'" autocomplete="current-password" class="block w-full rounded-lg border border-stone-300 bg-white px-4 py-3 pr-28 text-base text-stone-950 shadow-sm outline-none transition focus:border-teal-700 focus:ring-4 focus:ring-teal-700/15" aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('password') ? 'password-error' : '' }}">
                <button type="button" class="absolute right-2 top-1/2 -translate-y-1/2 rounded-md px-3 py-2 text-sm font-bold text-teal-800 transition hover:bg-teal-50 focus:outline-none focus:ring-4 focus:ring-teal-700/20" x-on:click="showPassword = ! showPassword" x-bind:aria-pressed="showPassword.toString()" x-bind:aria-label="showPassword ? 'Hide password' : 'Show password'">
                    <span x-text="showPassword ? 'Hide' : 'Show'"></span>
                </button>
            </div>
            @error('password')
                <p id="password-error" class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between gap-4">
            <label class="inline-flex items-center gap-2 text-sm font-semibold text-stone-700">
                <input name="remember" type="checkbox" value="1" class="size-4 rounded border-stone-300 text-teal-700 focus:ring-teal-700">
                Remember me
            </label>
            <a href="{{ route('password.request') }}" class="text-sm font-bold text-teal-800 underline-offset-4 hover:underline focus:outline-none focus:ring-4 focus:ring-teal-700/20">Forgot password?</a>
        </div>

        <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-teal-700 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-teal-900/10 transition hover:bg-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-700/25">
            Sign in
        </button>
    </form>

    @if ($registrationEnabled)
        <p class="mt-6 text-center text-sm text-stone-600">
            Need an account?
            <a href="{{ route('register') }}" class="font-bold text-teal-800 underline-offset-4 hover:underline focus:outline-none focus:ring-4 focus:ring-teal-700/20">Register</a>
        </p>
    @endif
</x-auth.layout>
