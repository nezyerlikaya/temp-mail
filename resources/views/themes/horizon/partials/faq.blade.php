@if (count($sections['faq'] ?? []) > 0)
    <section class="border-b border-stone-200 bg-white py-10">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            @foreach ($sections['faq'] as $section)
                <h2 class="text-2xl font-extrabold text-stone-950">{{ $section['title'] }}</h2>
                @if ($section['subtitle'])
                    <p class="mt-2 text-stone-600">{{ $section['subtitle'] }}</p>
                @endif
                <div class="mt-6 divide-y divide-stone-200" x-data="{ open: null }">
                    @foreach ($section['items'] as $index => $item)
                        <div class="py-3">
                            <button type="button" class="flex w-full items-center justify-between gap-4 py-2 text-start font-extrabold text-stone-950 focus:outline-none focus:ring-4 focus:ring-emerald-600/25" x-on:click="open === {{ $index }} ? open = null : open = {{ $index }}" x-bind:aria-expanded="open === {{ $index }}">
                                <span>{{ $item['title'] }}</span>
                                <span aria-hidden="true">+</span>
                            </button>
                            <div class="pt-2 text-sm leading-7 text-stone-600" x-show="open === {{ $index }}" x-cloak>{{ $item['content'] }}</div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </section>
@endif
