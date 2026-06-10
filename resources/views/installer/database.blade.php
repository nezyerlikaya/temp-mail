<x-installer.layout step="2" title="Database Setup" subtitle="Enter the database your hosting panel created for this Temp Mail SaaS. We test the connection before saving anything.">
    <x-error-summary />

    <div class="mb-6 rounded-lg border border-sky-200 bg-sky-50 p-4 text-sm text-sky-950">
        Missing database drivers are shown before submit. SQLite is optional and is unavailable unless <span class="font-semibold">pdo_sqlite</span> exists.
    </div>

    <form method="POST" action="{{ route('install.database.store') }}" x-data="{ submitting: false, showPassword: false }" x-on:submit="if (submitting) { $event.preventDefault(); return; } submitting = true" x-bind:aria-busy="submitting.toString()" x-bind:class="{ 'pointer-events-none opacity-70': submitting }" novalidate>
        @csrf

        <div class="grid gap-5">
            <div>
                <label for="connection" class="block text-sm font-bold text-stone-900">Connection</label>
                <select id="connection" name="connection" class="mt-2 block w-full rounded-lg border border-stone-300 bg-white px-4 py-3 text-base text-stone-950 shadow-sm outline-none transition focus:border-teal-700 focus:ring-4 focus:ring-teal-700/15" aria-invalid="{{ $errors->has('connection') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('connection') ? 'connection-error' : '' }}">
                    @foreach ($connections as $key => $connection)
                        <option value="{{ $key }}" @selected(old('connection', 'mysql') === $key) @disabled(! $connection['available'])>
                            {{ $connection['label'] }}{{ ! $connection['available'] ? ' · '.$connection['driver'].' missing' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('connection')
                    <p id="connection-error" class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-[1fr_140px]">
                <x-form.input name="host" label="Host" value="localhost" autocomplete="off" />
                <x-form.input name="port" label="Port" type="number" value="3306" inputmode="numeric" autocomplete="off" />
            </div>

            <x-form.input name="database" label="Database" autocomplete="off" />
            <x-form.input name="username" label="Username" autocomplete="username" />

            <div>
                <label for="password" class="block text-sm font-bold text-stone-900">Password</label>
                <div class="relative mt-2">
                    <input id="password" name="password" x-bind:type="showPassword ? 'text' : 'password'" autocomplete="current-password" class="block w-full rounded-lg border border-stone-300 bg-white px-4 py-3 pr-28 text-base text-stone-950 shadow-sm outline-none transition focus:border-teal-700 focus:ring-4 focus:ring-teal-700/15" aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}" aria-describedby="{{ $errors->has('password') ? 'password-error' : '' }}">
                    <button type="button" class="absolute right-2 top-1/2 -translate-y-1/2 rounded-md px-3 py-2 text-sm font-bold text-teal-800 transition hover:bg-teal-50 focus:outline-none focus:ring-4 focus:ring-teal-700/20" x-on:click="showPassword = ! showPassword" x-bind:aria-pressed="showPassword.toString()" x-bind:aria-label="showPassword ? 'Hide database password' : 'Show database password'">
                        <span x-text="showPassword ? 'Hide' : 'Show'"></span>
                    </button>
                </div>
                @error('password')
                    <p id="password-error" class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('install.readiness') }}" class="inline-flex items-center justify-center rounded-lg border border-stone-300 px-5 py-3 text-sm font-bold text-stone-800 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-stone-300">Back</a>
            <button type="submit" x-bind:disabled="submitting" class="inline-flex items-center justify-center rounded-lg bg-teal-700 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-teal-900/10 transition hover:bg-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-700/25 disabled:cursor-wait disabled:bg-teal-900">
                <span x-show="! submitting">Test and save database</span>
                <span x-cloak x-show="submitting">Testing connection...</span>
            </button>
        </div>
    </form>
</x-installer.layout>
