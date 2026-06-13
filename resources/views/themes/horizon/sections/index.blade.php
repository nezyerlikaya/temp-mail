@props(['sections' => []])

<div class="space-y-8">
    @foreach ($sections as $section)
        <section class="rounded-lg border border-stone-200 bg-white p-6">
            <h2 class="text-xl font-extrabold text-stone-950">{{ $section->title ?? 'Section' }}</h2>
        </section>
    @endforeach
</div>
