@props(['sections' => []])

<div class="space-y-6">
    @foreach ($sections as $section)
        <section class="border border-stone-200 p-4">
            <h2 class="text-lg font-extrabold text-stone-950">{{ $section->title ?? 'Section' }}</h2>
        </section>
    @endforeach
</div>
