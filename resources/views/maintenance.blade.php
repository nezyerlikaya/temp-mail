<x-auth.layout title="Scheduled maintenance">
    <div class="text-center">
        <span class="mx-auto grid size-12 place-items-center rounded-full bg-amber-100 text-amber-900" aria-hidden="true">
            <i data-lucide="wrench" class="size-5"></i>
        </span>
        <p class="mt-5 text-xs font-bold uppercase text-amber-800">Scheduled maintenance</p>
        <h1 class="mt-2 text-2xl font-extrabold text-stone-950">We will be back shortly</h1>
        <p class="mt-3 text-sm leading-6 text-stone-600">{{ $message }}</p>
        <a href="{{ route('login') }}" class="mt-6 inline-flex min-h-10 items-center justify-center rounded-md border border-stone-300 px-4 text-sm font-bold text-stone-700 transition hover:bg-stone-50 focus:outline-none focus:ring-4 focus:ring-teal-700/20">Administrator sign in</a>
    </div>
</x-auth.layout>
