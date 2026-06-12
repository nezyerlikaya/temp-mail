@props(['canCreate' => false])

<div class="rounded-lg border border-dashed border-stone-300 bg-white p-8 text-center">
    <p class="text-lg font-extrabold text-stone-950">No email templates yet</p>
    <p class="mx-auto mt-2 max-w-xl text-sm text-stone-600">Create language-specific system templates for password resets, alerts, updates, and support notifications.</p>
    @if ($canCreate)
        <a href="{{ route('admin.email-templates.create') }}" class="mt-5 inline-flex min-h-11 items-center justify-center rounded-lg bg-stone-950 px-4 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-teal-600/20">Create first template</a>
    @endif
</div>
