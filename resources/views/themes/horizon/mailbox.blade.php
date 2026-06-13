@extends('themes.horizon.layouts.public')

@section('content')
    <section class="bg-[#f4f7f6] py-8">
        <div class="mx-auto grid max-w-7xl gap-5 px-4 sm:px-6 lg:grid-cols-[.9fr_1.1fr] lg:px-8">
            <div class="space-y-5">
                @include('themes.horizon.partials.mailbox-status')
                @include('themes.horizon.partials.message-list')
            </div>
            @include('themes.horizon.partials.message-preview')
        </div>
    </section>
@endsection
