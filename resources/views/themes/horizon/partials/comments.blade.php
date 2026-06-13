<section class="mt-12 border-t border-stone-200 pt-8" aria-labelledby="comments-title">
    <h2 id="comments-title" class="text-2xl font-extrabold text-stone-950">{{ $translations['blog.comments.title'] }}</h2>
    @if (session('status'))<p class="mt-4 border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-900" role="status">{{ session('status') }}</p>@endif
    @foreach ($comments as $comment)
        <article class="mt-5 border-s-4 border-emerald-700 bg-white p-5">
            <p class="font-extrabold text-stone-950">{{ $comment['author'] }}</p>
            <p class="mt-1 text-xs font-semibold text-stone-500">{{ $comment['created_at'] }}</p>
            <div class="mt-3 text-sm leading-7 text-stone-700">{!! $comment['content'] !!}</div>
            @foreach ($comment['replies'] as $reply)
                <div class="mt-4 ms-6 border-s border-stone-200 ps-4">
                    <p class="font-bold">{{ $reply['author'] }}</p>
                    <div class="mt-2 text-sm leading-6 text-stone-700">{!! $reply['content'] !!}</div>
                </div>
            @endforeach
        </article>
    @endforeach
    @if ($comments_open)
        <form method="POST" action="{{ $comment_action }}" class="mt-8 grid gap-5 border border-stone-200 bg-white p-6" x-data="{ submitting: false }" x-on:submit="if (submitting) $event.preventDefault(); submitting = true" x-bind:aria-busy="submitting">
            @csrf
            @if ($errors->any())<div class="border border-red-300 bg-red-50 p-4 text-sm text-red-900" role="alert"><p class="font-extrabold">Please correct the highlighted fields.</p></div>@endif
            @guest
                <label class="grid gap-2 text-sm font-bold">{{ $translations['blog.comments.name'] }}<input name="author_name" value="{{ old('author_name') }}" autocomplete="name" @class(['min-h-11 border px-3 focus:outline-none focus:ring-4 focus:ring-emerald-600/25', 'border-red-500' => $errors->has('author_name'), 'border-stone-300' => ! $errors->has('author_name')]) aria-invalid="{{ $errors->has('author_name') ? 'true' : 'false' }}"></label>
                <label class="grid gap-2 text-sm font-bold">{{ $translations['blog.comments.email'] }}<input type="email" name="author_email" value="{{ old('author_email') }}" autocomplete="email" inputmode="email" @class(['min-h-11 border px-3 focus:outline-none focus:ring-4 focus:ring-emerald-600/25', 'border-red-500' => $errors->has('author_email'), 'border-stone-300' => ! $errors->has('author_email')]) aria-invalid="{{ $errors->has('author_email') ? 'true' : 'false' }}"></label>
            @endguest
            <label class="grid gap-2 text-sm font-bold">{{ $translations['blog.comments.content'] }}<textarea name="content" rows="5" @class(['border px-3 py-3 focus:outline-none focus:ring-4 focus:ring-emerald-600/25', 'border-red-500' => $errors->has('content'), 'border-stone-300' => ! $errors->has('content')]) aria-invalid="{{ $errors->has('content') ? 'true' : 'false' }}">{{ old('content') }}</textarea></label>
            <button class="min-h-11 bg-stone-950 px-5 py-3 text-sm font-extrabold text-white focus:outline-none focus:ring-4 focus:ring-stone-950/25 disabled:opacity-60" x-bind:disabled="submitting"><span x-show="!submitting">{{ $translations['blog.comments.submit'] }}</span><span x-cloak x-show="submitting">Submitting...</span></button>
        </form>
    @else
        <p class="mt-5 text-sm font-semibold text-stone-600">{{ $translations['blog.comments.closed'] }}</p>
    @endif
</section>
