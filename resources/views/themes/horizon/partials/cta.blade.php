@if (count($sections['cta'] ?? []) > 0 || count($sections['feature_grid'] ?? []) > 0 || count($sections['trust_security'] ?? []) > 0 || count($sections['abuse_notice'] ?? []) > 0 || count($sections['cookie_notice'] ?? []) > 0)
    <section class="bg-[#f4f7f6] py-10">
        <div class="mx-auto grid max-w-7xl gap-4 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
            @foreach ([...($sections['cta'] ?? []), ...($sections['feature_grid'] ?? []), ...($sections['trust_security'] ?? []), ...($sections['abuse_notice'] ?? []), ...($sections['cookie_notice'] ?? [])] as $section)
                <article class="border border-stone-200 bg-white p-5">
                    <h2 class="text-lg font-extrabold text-stone-950">{{ $section['title'] }}</h2>
                    @if ($section['content'])
                        <p class="mt-2 text-sm leading-6 text-stone-600">{{ $section['content'] }}</p>
                    @endif
                    @if (($section['button_label'] ?? null) && ($section['button_url'] ?? null))
                        <a href="{{ $section['button_url'] }}" class="mt-4 inline-flex font-extrabold text-emerald-800 focus:outline-none focus:ring-4 focus:ring-emerald-600/25">{{ $section['button_label'] }}</a>
                    @endif
                </article>
            @endforeach
        </div>
    </section>
@endif
