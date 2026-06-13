<article class="flex h-full flex-col border border-stone-200 bg-white">
    @if ($post['image'])
        <img src="{{ $post['image']['url'] }}" alt="{{ $post['image']['alt'] }}" width="{{ $post['image']['width'] }}" height="{{ $post['image']['height'] }}" class="aspect-[16/9] w-full object-cover" loading="lazy">
    @endif
    <div class="flex flex-1 flex-col p-5">
        <div class="flex flex-wrap items-center gap-3 text-xs font-bold text-stone-500">
            @if ($post['category'])<a href="{{ $post['category']['url'] }}" class="text-emerald-800 hover:underline">{{ $post['category']['name'] }}</a>@endif
            @if ($post['published_at'])<time>{{ $post['published_at'] }}</time>@endif
        </div>
        <h2 class="mt-3 text-xl font-extrabold text-stone-950"><a href="{{ $post['url'] }}" class="focus:outline-none focus:ring-4 focus:ring-emerald-600/25">{{ $post['title'] }}</a></h2>
        @if ($post['excerpt'])<p class="mt-3 line-clamp-3 text-sm leading-6 text-stone-600">{{ $post['excerpt'] }}</p>@endif
        <a href="{{ $post['url'] }}" class="mt-auto pt-5 text-sm font-extrabold text-emerald-800 hover:underline focus:outline-none focus:ring-4 focus:ring-emerald-600/25">{{ $translations['blog.read_more'] }}</a>
    </div>
</article>
