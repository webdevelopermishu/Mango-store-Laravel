@extends('layouts.storefront')

@section('content')
<section class="hero">
    <div class="container hero__inner">
        <div>
            <p class="eyebrow">{{ \App\Support\Branding::tagline() ?? config("store.name") }}</p>
            <h1 class="display">{{ __('storefront.home.hero_title') }}</h1>
            <p class="lead">{{ __('storefront.home.hero_text') }}</p>
            <a class="btn btn--primary" href="{{ route('shop') }}">{{ __('storefront.home.shop_now') }}</a>
        </div>
    </div>
</section>

@if ($categories->isNotEmpty())
<section class="container section" aria-labelledby="cat-heading">
    <h2 id="cat-heading" class="section-title">{{ __('storefront.home.categories') }}</h2>
    <div class="category-grid">
        @foreach ($categories as $category)
            <a class="category-card" href="{{ route('shop.category', $category) }}">
                @if ($category->image_url)
                    <img src="{{ $category->image_url }}" alt="" loading="lazy" width="400" height="300">
                @else
                    <span class="category-card__placeholder" aria-hidden="true"></span>
                @endif
                <span class="category-card__name">{{ $category->name }}</span>
            </a>
        @endforeach
    </div>
</section>
@endif

@if ($heroProducts->isNotEmpty())
<section class="container section" aria-labelledby="mango-heading">
    <h2 id="mango-heading" class="section-title">{{ __('storefront.home.section.mango') }}</h2>
    <div class="product-grid">
        @foreach ($heroProducts as $product)
            <x-product-card :product="$product" />
        @endforeach
    </div>
</section>
@endif

@if ($featuredProducts->isNotEmpty())
<section class="container section" aria-labelledby="popular-heading">
    <h2 id="popular-heading" class="section-title">{{ __('storefront.home.section.popular') }}</h2>
    <div class="product-grid">
        @foreach ($featuredProducts as $product)
            <x-product-card :product="$product" />
        @endforeach
    </div>
</section>
@endif
@endsection
