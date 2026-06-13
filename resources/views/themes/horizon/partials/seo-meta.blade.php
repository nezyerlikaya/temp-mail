<title>{{ $seo['title'] }}</title>
<meta name="description" content="{{ $seo['description'] }}">
<meta name="robots" content="{{ $seo['robots'] }}">
<link rel="canonical" href="{{ $seo['canonical'] }}">
<meta property="og:type" content="{{ $seo['og_type'] ?? 'website' }}">
<meta property="og:title" content="{{ $seo['og_title'] }}">
<meta property="og:description" content="{{ $seo['og_description'] }}">
<meta property="og:url" content="{{ $seo['canonical'] }}">
@if ($seo['og_image'])<meta property="og:image" content="{{ $seo['og_image'] }}">@endif
<meta name="twitter:card" content="{{ $seo['twitter_card'] }}">
<meta name="twitter:title" content="{{ $seo['twitter_title'] ?? $seo['og_title'] }}">
<meta name="twitter:description" content="{{ $seo['twitter_description'] ?? $seo['og_description'] }}">
@if ($seo['twitter_image'] ?? null)<meta name="twitter:image" content="{{ $seo['twitter_image'] }}">@endif
@foreach ($seo['hreflang'] ?? [] as $alternate)<link rel="alternate" hreflang="{{ $alternate['locale'] }}" href="{{ $alternate['url'] }}">@endforeach
@if ($seo['schema'])<script type="application/ld+json">{!! json_encode($seo['schema'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>@endif
