@props(['class' => ''])

<div {{ $attributes->merge(['class' => 'rounded-lg border border-dashed border-stone-300 bg-white px-6 py-10 text-center '.$class]) }} role="status">
    <div class="mx-auto grid size-11 place-items-center rounded-full bg-stone-100 text-stone-500">
        <i data-lucide="notebook-pen" class="size-5" aria-hidden="true"></i>
    </div>
    <p class="mt-4 text-sm font-extrabold text-stone-950">No blog posts found</p>
    <p class="mt-1 text-sm leading-6 text-stone-600">Create language-specific posts manually, then use filters to manage the publishing queue.</p>
</div>
