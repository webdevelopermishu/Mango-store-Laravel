@props(['product'])
@php
    $variants = $product->variants->where('is_active', true)->values();
    $live = $product->status === \App\Models\Product::STATUS_PUBLISHED;
    $purchasable = $live && $variants->contains(fn ($v) => $v->isInStock());
    $defaultVariant = $variants->first(fn ($v) => $v->isInStock()) ?? $variants->first();
    $formId = 'atc-'.$product->id;
@endphp

<form method="post" action="{{ route('cart.add') }}" class="add-to-cart" id="{{ $formId }}"
      data-add-to-cart
      data-error-message="{{ __('storefront.cart.error') }}"
      data-product-name="{{ $product->name }}">
    @csrf
    <input type="hidden" name="request_id" value="">

    @if ($variants->count() > 1)
        <fieldset class="variant-picker">
            <legend>{{ __('storefront.product.choose_size') }}</legend>
            @foreach ($variants as $variant)
                <label class="variant-option">
                    <input type="radio" name="variant_id" value="{{ $variant->id }}"
                           @checked($defaultVariant && $variant->id === $defaultVariant->id)
                           @disabled(! $variant->isInStock())>
                    <span class="variant-option__label">{{ $variant->label }}</span>
                    <span class="variant-option__price">{{ \App\Support\Money::format($variant->currentPricePaisa()) }}</span>
                </label>
            @endforeach
        </fieldset>
    @elseif ($defaultVariant)
        <input type="hidden" name="variant_id" value="{{ $defaultVariant->id }}">
    @endif

    <div class="add-to-cart__row">
        <label class="qty">
            <span class="sr-only">{{ __('storefront.product.quantity') }}</span>
            <input type="number" name="quantity" value="1" min="1" max="{{ config('store.cart.max_qty_per_line') }}"
                   inputmode="numeric" @disabled(! $purchasable)>
        </label>

        <button type="submit" class="atc-button" data-state="{{ $purchasable ? 'idle' : 'out' }}" @disabled(! $purchasable)>
            <span class="atc-label atc-label--idle">
                <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" class="atc-icon"><path fill="currentColor" d="M7 18a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm10 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4ZM1 2h3l2.7 12.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.5L23 6H6.2l-.6-2.8A1 1 0 0 0 4.6 2H1Z"/></svg>
                {{ __('storefront.cart.add') }}
            </span>
            <span class="atc-label atc-label--loading" aria-hidden="true">
                <span class="atc-spinner"></span>{{ __('storefront.cart.adding') }}
            </span>
            <span class="atc-label atc-label--success" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="18" height="18" class="atc-icon"><path fill="currentColor" d="m9 16.2-3.5-3.5L4 14.2l5 5 11-11-1.5-1.5z"/></svg>
                {{ __('storefront.cart.added') }}
            </span>
            <span class="atc-label atc-label--out" aria-hidden="true">{{ __('storefront.cart.out_of_stock') }}</span>
        </button>
    </div>

    <p class="atc-status" role="status" aria-live="polite"></p>
</form>
