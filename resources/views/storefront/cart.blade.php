@extends('layouts.storefront')

@section('meta_title', \App\Support\Branding::metaTitle(__('storefront.cart.title')))
@push('head')<meta name="robots" content="noindex">@endpush

@section('content')
<div class="container section">
    <h1 class="page-title">{{ __('storefront.cart.title') }}</h1>

    @if ($lines->isEmpty())
        <p class="empty">{{ __('storefront.cart.empty') }}</p>
        <a class="btn btn--primary" href="{{ route('shop') }}">{{ __('storefront.home.shop_now') }}</a>
    @else
        <ul class="cart-lines">
            @foreach ($lines as $line)
                @php $variant = $line['variant']; $img = $variant->product->images->first()?->url; @endphp
                <li class="cart-line">
                    @if ($img)
                        <img src="{{ $img }}" alt="" width="72" height="72">
                    @else
                        <span class="cart-line__placeholder" aria-hidden="true"></span>
                    @endif
                    <div class="cart-line__info">
                        <a href="{{ route('product.show', $variant->product) }}"><strong>{{ $variant->product->name }}</strong></a>
                        <span class="muted">{{ $variant->label }} · {{ \App\Support\Money::format($variant->currentPricePaisa()) }}</span>
                        @unless ($line['in_stock'])
                            <span class="error small">{{ __('storefront.cart.low_stock') }}</span>
                        @endunless
                    </div>
                    <form method="post" action="{{ route('cart.update', $variant) }}" class="cart-line__qty">
                        @csrf
                        @method('PATCH')
                        <label>
                            <span class="sr-only">{{ __('storefront.cart.quantity') }}: {{ $variant->product->name }}</span>
                            <input type="number" name="quantity" value="{{ $line['quantity'] }}" min="0" max="{{ config('store.cart.max_qty_per_line') }}" inputmode="numeric">
                        </label>
                        <button class="btn btn--ghost btn--small" type="submit">{{ __('storefront.cart.update') }}</button>
                    </form>
                    <span class="cart-line__total">{{ \App\Support\Money::format($line['line_total']) }}</span>
                    <form method="post" action="{{ route('cart.remove', $variant) }}" class="cart-line__remove">
                        @csrf
                        @method('DELETE')
                        <button class="link-button" type="submit">{{ __('storefront.cart.remove') }}</button>
                    </form>
                </li>
            @endforeach
        </ul>

        <div class="cart-footer">
            <p class="cart-subtotal">{{ __('storefront.cart.subtotal') }}: <strong>{{ \App\Support\Money::format($subtotal) }}</strong></p>
            <div class="cart-footer__actions">
                <a class="btn btn--ghost" href="{{ route('shop') }}">{{ __('storefront.cart.keep_shopping') }}</a>
                <a class="btn btn--primary" href="{{ route('checkout.create') }}">{{ __('storefront.cart.checkout') }}</a>
            </div>
        </div>
    @endif
</div>
@endsection
