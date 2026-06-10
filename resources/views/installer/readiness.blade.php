<x-installer.layout step="1" title="System Readiness" subtitle="We check the essentials before asking for credentials, so the setup never fails with a blank screen or driver surprise.">
    <div class="space-y-4">
        @foreach ($checklist as $item)
            <div class="flex items-start gap-4 rounded-lg border {{ $item['passed'] ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50' }} p-4">
                <span class="grid size-9 shrink-0 place-items-center rounded-full {{ $item['passed'] ? 'bg-emerald-600 text-white' : 'bg-amber-500 text-stone-950' }}" aria-hidden="true">{{ $item['passed'] ? '✓' : '!' }}</span>
                <div>
                    <p class="font-bold text-stone-950">{{ $item['label'] }}</p>
                    <p class="mt-1 text-sm text-stone-700">{{ $item['detail'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-8 rounded-lg border border-stone-200 bg-stone-50 p-4">
        <p class="text-sm font-bold text-stone-950">Available database paths</p>
        <div class="mt-3 grid gap-3 sm:grid-cols-3">
            @foreach ($connections as $connection)
                <div class="rounded-lg border border-stone-200 bg-white p-3">
                    <p class="font-bold">{{ $connection['label'] }}</p>
                    <p class="mt-1 text-sm {{ $connection['available'] ? 'text-emerald-700' : 'text-red-700' }}">{{ $connection['available'] ? 'Driver ready' : $connection['driver'].' missing' }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-8 flex justify-end">
        <a href="{{ route('install.database') }}" class="inline-flex items-center justify-center rounded-lg bg-teal-700 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-teal-900/10 transition hover:bg-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-700/25">
            Continue to database
        </a>
    </div>
</x-installer.layout>
