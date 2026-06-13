@extends('themes.horizon.layouts.public')
@section('content')
<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    @include('themes.horizon.partials.breadcrumbs')
    <header class="max-w-3xl"><h1 class="text-3xl font-extrabold text-stone-950 sm:text-4xl">{{ $page_heading }}</h1><p class="mt-4 text-base leading-7 text-stone-600">{{ $page_description }}</p></header>
    @if ($posts['items'])<div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">@foreach ($posts['items'] as $post)@include('themes.horizon.partials.post-card', ['post' => $post])@endforeach</div>@else<div class="mt-8 border border-stone-200 bg-white p-8"><h2 class="text-xl font-extrabold">{{ $translations['blog.empty.title'] }}</h2><p class="mt-2 text-stone-600">{{ $translations['blog.empty.body'] }}</p></div>@endif
    @include('themes.horizon.partials.pagination')
</section>
@endsection
