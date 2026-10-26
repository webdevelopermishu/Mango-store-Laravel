@extends('layouts.storefront')

@section('meta_title', \App\Support\Branding::metaTitle($product->localizedOrNull('seo_title') ?? $product->name))
@section('meta_description', \App\Support\Branding::metaDescription($product->localizedOrNull('seo_description') ?? $product->localizedOrNull('short_description')))

@push('head')
@php
    $firstImage = $product->images->first()?->url;
    $minPrice = $product->minPricePaisa();
    $available = $product->status === \App\Models\Product::STATUS_PUBLISHED;
@endphp
<script type="application/ld+json">
{!! json_encode(array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => $product->name,
    "description" => $product->localizedOrNull("short_description"),
    'sku' => $product->sku,
    'image' => $firstImage,
    'inLanguage' => app()->getLocale(),
    'url' => url()->current(),
    'offers' => $minPrice !== null ? [
        '@type' => 'Offer',
        'priceCurrency' => 'BDT',
        'price' => number_format($minPrice / 100, 2, '.', ''),
        'availability' => $available ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        'url' => url()->current(),
    ] : null,
]), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
</script>
@endpush

@section('content')
<div class="container section">
    @php
        $crumbCategory = $product->categories->first();
    @endphp
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <ol>
            <li><a href="{{ route('home') }}">{{ __('storefront.breadcrumb.home') }}</a></li>
            <li><a href="{{ route('shop') }}">{{ __('storefront.breadcrumb.shop') }}</a></li>
            @if ($crumbCategory)
                <li><a href="{{ route('shop.category', $crumbCategory) }}">{{ $crumbCategory->name }}</a></li>
            @endif
            <li aria-current="page">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="product-detail">
        <div class="product-detail__media">
            @if ($product->images->isNotEmpty())
                <img src="{{ $product->images->first()->url }}" alt="{{ $product->name }}" width="720" height="720">
                @if ($product->images->count() > 1)
                    <ul class="thumbs">
                        @foreach ($product->images as $image)
                            <li><img src="{{ $image->url }}" alt="{{ $product->name }}" loading="lazy" width="120" height="120"></li>
                        @endforeach
                    </ul>
                @endif
            @else
                <div class="product-card__placeholder product-card__placeholder--large" aria-hidden="true"></div>
            @endif
        </div>

        <div class="product-detail__info">
            <h1 class="page-title">{{ $product->name }}</h1>
            @if ($product->localizedOrNull("short_description"))<p class="lead">{{ $product->localizedOrNull("short_description") }}</p>@endif

            <p class="badges">
                @if ($product->is_seasonal)
                    <span class="badge @unless($product->isInSeason()) badge--muted @endunless">
                        {{ $product->isInSeason() ? __('storefront.product.in_season') : __('storefront.product.off_season') }}
                    </span>
                @endif
            </p>

            <x-add-to-cart :product="$product" />

            @if ($product->sku)
                <p class="muted">{{ __('storefront.product.sku') }}: {{ $product->sku }}</p>
            @endif

            <section class="prose" aria-labelledby="desc-heading">
                <h2 id="desc-heading" class="section-title">{{ __('storefront.product.description') }}</h2>
                {!! nl2br(e($product->localizedOrNull("description") ?? "")) !!}
            </section>
        </div>
    </div>

    @if ($related->isNotEmpty())
        <section class="section" aria-labelledby="related-heading">
            <h2 id="related-heading" class="section-title">{{ __('storefront.product.related') }}</h2>
            <div class="product-grid">
                @foreach ($related as $item)
                    <x-product-card :product="$item" />
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
