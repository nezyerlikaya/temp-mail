@extends('themes.legacy.layouts.public')

@section('content')
    <section class="bg-yellow-50 py-8">
        <div class="mx-auto grid max-w-5xl gap-5 px-4 lg:grid-cols-[.9fr_1.1fr]">
            <div class="space-y-5">
                @include('themes.legacy.partials.mailbox-status')
                @include('themes.legacy.partials.message-list')
            </div>
            @include('themes.legacy.partials.message-preview')
        </div>
    </section>
@endsection
