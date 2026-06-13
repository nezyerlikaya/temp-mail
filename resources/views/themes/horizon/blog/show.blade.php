@extends('themes.horizon.layouts.public')
@section('content')
<article class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
    @include('themes.horizon.partials.breadcrumbs')
    @if ($post['category'])<a href="{{ $post['category']['url'] }}" class="text-sm font-extrabold text-emerald-800 hover:underline">{{ $post['category']['name'] }}</a>@endif
    <h1 class="mt-3 text-3xl font-extrabold leading-tight text-stone-950 sm:text-5xl">{{ $post['title'] }}</h1>
    @if ($post['excerpt'])<p class="mt-5 text-lg leading-8 text-stone-600">{{ $post['excerpt'] }}</p>@endif
    <div class="mt-6 flex flex-wrap items-center gap-3 text-sm font-semibold text-stone-500">@if ($post['author'])<span>{{ $post['author']['name'] }}</span>@endif @if ($post['published_at'])<time>{{ $post['published_at'] }}</time>@endif</div>
    @if ($post['image'])<img src="{{ $post['image']['url'] }}" alt="{{ $post['image']['alt'] }}" width="{{ $post['image']['width'] }}" height="{{ $post['image']['height'] }}" class="mt-8 aspect-[16/9] w-full object-cover">@endif
    <div class="prose prose-stone mt-10 max-w-none leading-8">{!! $post['content_html'] !!}</div>
    @if ($post['tags'])<div class="mt-8 flex flex-wrap gap-2">@foreach ($post['tags'] as $tag)<a href="{{ $tag['url'] }}" class="border border-stone-200 bg-white px-3 py-2 text-sm font-bold">#{{ $tag['name'] }}</a>@endforeach</div>@endif
    @include('themes.horizon.partials.comments')
</article>
@if ($related_posts)<section class="border-t border-stone-200 bg-white"><div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8"><h2 class="text-2xl font-extrabold">{{ $translations['blog.related.title'] }}</h2><div class="mt-6 grid gap-6 md:grid-cols-3">@foreach ($related_posts as $post)@include('themes.horizon.partials.post-card', ['post' => $post])@endforeach</div></div></section>@endif
@endsection
