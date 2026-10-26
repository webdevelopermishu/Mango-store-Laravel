@extends('layouts.storefront')

@php
    $pageTitle = $category ? $category->name : __('storefront.shop.title');
@endphp

@section('meta_title', \App\Support\Branding::metaTitle($category?->localizedOrNull('seo_title') ?? $pageTitle))
@section('meta_description', \App\Support\Branding::metaDescription($category?->localizedOrNull('seo_description') ?? $category?->localizedOrNull('description')))

@section('content')
<div class="container section">
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <ol>
            <li><a href="{{ route('home') }}">{{ __('storefront.breadcrumb.home') }}</a></li>
            <li><a href="{{ route('shop') }}">{{ __('storefront.breadcrumb.shop') }}</a></li>
            @if ($category)<li aria-current="page">{{ $category->name }}</li>@endif
        </ol>
    </nav>

    <h1 class="page-title">{{ $pageTitle }}</h1>

    <nav class="chip-nav" aria-label="{{ __('storefront.shop.title') }}">
        <a href="{{ route('shop') }}" class="chip @unless($category) is-active @endunless">{{ __('storefront.shop.all') }}</a>
        @foreach ($navCategories as $nav)
            <a href="{{ route('shop.category', $nav) }}" class="chip @if($category && $category->id === $nav->id) is-active @endif">{{ $nav->name }}</a>
            @foreach ($nav->children as $child)
                <a href="{{ route('shop.category', $child) }}" class="chip chip--sub @if($category && $category->id === $child->id) is-active @endif">{{ $child->name }}</a>
            @endforeach
        @endforeach
    </nav>

    <div class="shop-layout">
        <aside class="filters" aria-label="{{ __('storefront.shop.apply') }}">
            <form method="get" action="{{ $category ? route('shop.category', $category) : route('shop') }}">
                <label class="field">
                    <span>{{ __('storefront.shop.search') }}</span>
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('storefront.shop.search_placeholder') }}">
                </label>
                <label class="field">
                    <span>{{ __('storefront.shop.min_price') }}</span>
                    <input type="number" name="min_price" min="0" step="1" value="{{ request('min_price') }}">
                </label>
                <label class="field">
                    <span>{{ __('storefront.shop.max_price') }}</span>
                    <input type="number" name="max_price" min="0" step="1" value="{{ request('max_price') }}">
                </label>
                <label class="field">
                    <span>{{ __('storefront.shop.sort') }}</span>
                    <select name="sort">
                        <option value="">{{ __('storefront.shop.sort_newest') }}</option>
                        <option value="price_asc" @selected(request('sort') === 'price_asc')>{{ __('storefront.shop.sort_price_asc') }}</option>
                        <option value="price_desc" @selected(request('sort') === 'price_desc')>{{ __('storefront.shop.sort_price_desc') }}</option>
                        <option value="name" @selected(request('sort') === 'name')>{{ __('storefront.shop.sort_name') }}</option>
                    </select>
                </label>
                <label class="checkbox">
                    <input type="checkbox" name="in_stock" value="1" @checked(request()->boolean('in_stock'))>
                    <span>{{ __('storefront.shop.in_stock_only') }}</span>
                </label>
                <button class="btn btn--primary" type="submit">{{ __('storefront.shop.apply') }}</button>
                <a class="btn btn--ghost" href="{{ $category ? route('shop.category', $category) : route('shop') }}">{{ __('storefront.shop.reset') }}</a>
            </form>
        </aside>

        <div>
            @if ($products->isEmpty())
                <p class="empty">{{ __('storefront.shop.no_results') }}</p>
            @else
                <div class="product-grid">
                    @foreach ($products as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
                <div class="pagination">{{ $products->links('vendor.pagination.store') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
