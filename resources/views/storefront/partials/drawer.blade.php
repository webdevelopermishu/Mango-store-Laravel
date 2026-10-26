{{-- Confirmation drawer shown after a successful add. public/js/storefront.js fills it in. --}}
<div class="cart-drawer" data-cart-drawer hidden>
    <div class="cart-drawer__backdrop" data-drawer-close></div>
    <div class="cart-drawer__panel" role="dialog" aria-modal="true" aria-labelledby="cart-drawer-title" tabindex="-1">
        <h2 id="cart-drawer-title" class="cart-drawer__title">{{ __('storefront.cart.added') }}</h2>
        <div class="cart-drawer__item">
            <img data-drawer-image alt="" width="72" height="72" hidden>
            <div>
                <p class="cart-drawer__name" data-drawer-name></p>
                <p class="muted" data-drawer-variant></p>
                <p>{{ __('storefront.cart.quantity') }}: <span data-drawer-qty></span></p>
            </div>
        </div>
        <p class="cart-drawer__subtotal">{{ __('storefront.cart.subtotal') }}: <strong data-drawer-subtotal></strong></p>
        <div class="cart-drawer__actions">
            <button type="button" class="btn btn--ghost" data-drawer-close>{{ __('storefront.cart.keep_shopping') }}</button>
            <a class="btn btn--primary" href="{{ route('cart.index') }}">{{ __('storefront.cart.view_cart') }}</a>
        </div>
    </div>
</div>
