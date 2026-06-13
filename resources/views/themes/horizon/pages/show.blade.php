@extends('themes.horizon.layouts.public')
@section('content')
<article class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
    @include('themes.horizon.partials.breadcrumbs')
    <h1 class="text-3xl font-extrabold text-stone-950 sm:text-5xl">{{ $page['title'] }}</h1>
    @if ($page['excerpt'])<p class="mt-5 text-lg leading-8 text-stone-600">{{ $page['excerpt'] }}</p>@endif
    @if ($page['image'])<img src="{{ $page['image']['url'] }}" alt="{{ $page['image']['alt'] }}" width="{{ $page['image']['width'] }}" height="{{ $page['image']['height'] }}" class="mt-8 aspect-[16/9] w-full object-cover">@endif
    <div class="prose prose-stone mt-10 max-w-none leading-8">{!! $page['content_html'] !!}</div>
    @if ($page['updated_at'])<p class="mt-10 border-t border-stone-200 pt-5 text-sm font-semibold text-stone-500">{{ $translations['page.updated'] }}: {{ $page['updated_at'] }}</p>@endif
</article>
@endsection
