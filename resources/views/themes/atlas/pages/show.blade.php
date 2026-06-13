@extends('themes.atlas.layout', ['title' => $page->title ?? 'Page'])

@section('content')
    <article class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-extrabold text-white">{{ $page->title ?? 'Page' }}</h1>
    </article>
@endsection
