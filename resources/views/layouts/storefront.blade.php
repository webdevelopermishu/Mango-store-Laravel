@php
            
    $locale = app()->getLocale();
    $cartCount = app(\App\Services\Cart::class)->count();
    $headerLogo = \App\Support\Branding::logoFor('header');
    $mobileLogo = \App\Support\Branding::logoFor('mobile');
    $footerLogo = \App\Support\Branding::logoFor('footer');
    $favicon = \App\Support\Branding::imageUrl('favicon');
    $ogImage = \App\Support\Branding::imageUrl('og_image');
    $path = request()->getPathInfo();
@endphp
<!doctype html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('meta_title', \App\Support\Branding::metaTitle())</title>
    <meta name="description" content="@yield('meta_description', \App\Support\Branding::metaDescription())">
    <link rel="canonical" href="{{ url($path) }}">

    @foreach (config('store.locales') as $alt)
        <link rel="alternate" hreflang="{{ $alt }}" href="{{ url(\App\Support\LocaleUrl::swap($path, $alt)) }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ url(\App\Support\LocaleUrl::swap($path, config('store.default_locale'))) }}">

    <meta property="og:site_name" content="{{ \App\Support\Branding::title() }}">
    <meta property="og:locale" content="{{ $locale === 'bn' ? 'bn_BD' : 'en_US' }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('meta_title', \App\Support\Branding::metaTitle())">
    <meta property="og:description" content="@yield('meta_description', \App\Support\Branding::metaDescription())">
    <meta property="og:url" content="{{ url()->current() }}">
    @if ($ogImage)<meta property="og:image" content="{{ $ogImage }}">@endif

    @if ($favicon)<link rel="icon" href="{{ $favicon }}">@endif

    {{-- Preload the two faces used on every page. Other weights load on demand. --}}
    <link rel="preload" href="{{ asset('fonts/hind-siliguri-bengali-400-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('fonts/dm-sans-latin-400-normal.woff2') }}" as="font" type="font/woff2" crossorigin>

    <link rel="stylesheet" href="{{ asset('css/storefront.css') }}">
    @stack('head')
</head>
<body>
<a class="skip-link" href="#main">{{ __('storefront.skip') }}</a>

<header class="site-header">
    <div class="container site-header__inner">
        <a class="brand" href="{{ route('home') }}" aria-label="{{ \App\Support\Branding::title() }}">
            @if ($headerLogo)
                <picture>
                    @if ($mobileLogo)<source media="(max-width: 640px)" srcset="{{ $mobileLogo }}">@endif
                    <img src="{{ $headerLogo }}" alt="{{ \App\Support\Branding::title() }}" height="44">
                </picture>
            @else
                <span class="brand__wordmark">{{ \App\Support\Branding::title() }}</span>
            @endif
        </a>

        <nav class="primary-nav" aria-label="{{ __('storefront.nav.main') }}">
            <a href="{{ route('home') }}" @if (request()->routeIs('home')) aria-current="page" @endif>{{ __('storefront.nav.home') }}</a>
            <a href="{{ route('shop') }}" @if (request()->routeIs('shop*')) aria-current="page" @endif>{{ __('storefront.nav.shop') }}</a>
            <a href="{{ route('order.track') }}" @if (request()->routeIs('order.*')) aria-current="page" @endif>{{ __('storefront.nav.track') }}</a>
        </nav>

        <div class="header-tools">
            <nav class="lang-switch" aria-label="{{ __('storefront.nav.language') }}">
                @foreach (config('store.locales') as $alt)
                    <a href="{{ \App\Support\LocaleUrl::switch(request(), $alt) }}"
                       lang="{{ $alt }}" hreflang="{{ $alt }}"
                       @if ($alt === $locale) class="is-active" aria-current="true" @endif>
                        {{ $alt === 'bn' ? 'বাংলা' : 'English' }}
                    </a>
                @endforeach
            </nav>

            <a class="cart-link" href="{{ route('cart.index') }}" aria-label="{{ __('storefront.nav.cart') }}, {{ __('storefront.cart.item_count', ['count' => $cartCount]) }}">
                <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path fill="currentColor" d="M7 18a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm10 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4ZM1 2h3l2.7 12.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.5L23 6H6.2l-.6-2.8A1 1 0 0 0 4.6 2H1Zm5.2 6h15.1l-1.5 6.5H8l-1.8-6.5Z"/></svg>
                <span class="cart-link__count" data-cart-count>{{ $cartCount }}</span>
            </a>
        </div>
    </div>
</header>

<main id="main">
    @if (session('status'))
        <div class="container flash flash--ok" role="status">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="container flash flash--error" role="alert">{{ $errors->first() }}</div>
    @endif

    @yield('content')
</main>

<footer class="site-footer">
    <div class="container site-footer__inner">
        @if ($footerLogo)
            <img src="{{ $footerLogo }}" alt="{{ \App\Support\Branding::title() }}" height="40">
        @else
            <strong>{{ \App\Support\Branding::title() }}</strong>
        @endif
        @if (\App\Support\Branding::tagline())<p>{{ \App\Support\Branding::tagline() }}</p>@endif
        <p class="site-footer__copy">© {{ date('Y') }} {{ \App\Support\Branding::title() }}. {{ __('storefront.footer.rights') }}</p>
    </div>
</footer>

@include('storefront.partials.drawer')

<script src="{{ asset('js/storefront.js') }}" defer></script>
@stack('scripts')
</body>
</html>
