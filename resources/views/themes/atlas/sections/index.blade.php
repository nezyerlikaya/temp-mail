@props(['sections' => []])

<div class="space-y-8">
    @foreach ($sections as $section)
        <section class="rounded-lg border border-white/10 bg-white/5 p-6">
            <h2 class="text-xl font-extrabold text-white">{{ $section->title ?? 'Section' }}</h2>
        </section>
    @endforeach
</div>
