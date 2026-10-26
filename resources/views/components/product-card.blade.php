@props(['product'])
@php
    $image = $product->images->first();
    $min = $product->minPricePaisa();
@endphp
<article class="product-card">
    <a class="product-card__media" href="{{ route('product.show', $product) }}" tabindex="-1" aria-hidden="true">
        @if ($image)
            <img src="{{ $image->url }}" alt="" loading="lazy" width="480" height="480">
        @else
            <span class="product-card__placeholder" aria-hidden="true"></span>
        @endif
        @unless ($product->isInSeason())
            <span class="badge badge--muted">{{ __('storefront.product.off_season') }}</span>
        @endunless
    </a>
    <div class="product-card__body">
        <h3 class="product-card__title"><a href="{{ route('product.show', $product) }}">{{ $product->name }}</a></h3>
        @if ($min !== null)
            <p class="product-card__price">{{ \App\Support\Money::format($min) }}</p>
        @endif
        <x-add-to-cart :product="$product" />
    </div>
</article>
