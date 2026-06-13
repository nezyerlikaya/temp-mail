@if (count($sections['blog_teaser'] ?? []) > 0)
    <section class="bg-white py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @foreach ($sections['blog_teaser'] as $section)
                <h2 class="text-2xl font-extrabold text-stone-950">{{ $section['title'] }}</h2>
                <div class="mt-5 grid gap-4 md:grid-cols-3">
                    @foreach ($section['posts'] as $post)
                        <article class="border border-stone-200 p-5">
                            <p class="text-xs font-bold text-stone-500">{{ $post['published_at'] }}</p>
                            <h3 class="mt-2 font-extrabold text-stone-950">{{ $post['title'] }}</h3>
                            @if ($post['excerpt'])
                                <p class="mt-2 text-sm text-stone-600">{{ $post['excerpt'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endforeach
        </div>
    </section>
@endif
