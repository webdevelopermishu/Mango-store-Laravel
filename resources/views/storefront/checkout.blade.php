@extends('layouts.storefront')

@section('meta_title', \App\Support\Branding::metaTitle(__('storefront.checkout.title')))
@push('head')<meta name="robots" content="noindex">@endpush

@use('App\Support\Money')
@php
    $zone = old('delivery_zone', array_key_first($fees));
    $method = old('payment_method', $methods[0]);
    $mobileMethods = array_values(array_intersect($methods, \App\Models\Order::MOBILE_METHODS));
@endphp

@section('content')
<div class="container section">
    <h1 class="page-title">{{ __('storefront.checkout.title') }}</h1>

    <form method="post" action="{{ route('checkout.store') }}" class="checkout" data-checkout>
        @csrf
        <input type="hidden" name="checkout_token" value="{{ $token }}">
        <input type="hidden" name="expected_subtotal" value="{{ $subtotal }}">

        <div class="checkout__main">
            <fieldset class="panel">
                <legend>{{ __('storefront.checkout.contact') }}</legend>
                <label class="field">
                    <span>{{ __('storefront.checkout.name') }}</span>
                    <input name="name" value="{{ old('name') }}" required maxlength="120" autocomplete="name">
                    @error('name')<span class="error">{{ $message }}</span>@enderror
                </label>
                <label class="field">
                    <span>{{ __('storefront.checkout.phone') }}</span>
                    <input name="phone" value="{{ old('phone') }}" required inputmode="tel" autocomplete="tel" placeholder="{{ __('storefront.checkout.phone_hint') }}">
                    @error('phone')<span class="error">{{ $message }}</span>@enderror
                </label>
                <label class="field">
                    <span>{{ __('storefront.checkout.email') }}</span>
                    <input type="email" name="email" value="{{ old('email') }}" maxlength="150" autocomplete="email">
                    @error('email')<span class="error">{{ $message }}</span>@enderror
                </label>
            </fieldset>

            <fieldset class="panel">
                <legend>{{ __('storefront.checkout.delivery') }}</legend>
                <label class="field">
                    <span>{{ __('storefront.checkout.address') }}</span>
                    <textarea name="address" rows="3" required maxlength="500" autocomplete="street-address">{{ old('address') }}</textarea>
                    @error('address')<span class="error">{{ $message }}</span>@enderror
                </label>
                <label class="field">
                    <span>{{ __('storefront.checkout.district') }}</span>
                    <input name="district" value="{{ old('district') }}" required maxlength="120" autocomplete="address-level2">
                    @error('district')<span class="error">{{ $message }}</span>@enderror
                </label>

                <div class="choice-group" role="radiogroup" aria-label="{{ __('storefront.checkout.zone') }}">
                    <p class="choice-group__label">{{ __('storefront.checkout.zone') }}</p>
                    @foreach ($fees as $zoneKey => $fee)
                        <label class="choice">
                            <input type="radio" name="delivery_zone" value="{{ $zoneKey }}" required @checked($zone === $zoneKey)
                                   data-fee-text="{{ Money::format($fee) }}" data-total-text="{{ Money::format($subtotal + $fee) }}">
                            <span class="choice__label">{{ __('storefront.checkout.zones.'.$zoneKey) }}</span>
                            <span class="choice__meta">{{ Money::format($fee) }}</span>
                        </label>
                    @endforeach
                    @error('delivery_zone')<span class="error">{{ $message }}</span>@enderror
                </div>
            </fieldset>

            <fieldset class="panel">
                <legend>{{ __('storefront.checkout.payment') }}</legend>
                <div class="choice-group" role="radiogroup" aria-label="{{ __('storefront.checkout.payment') }}">
                    @foreach ($methods as $methodKey)
                        <label class="choice">
                            <input type="radio" name="payment_method" value="{{ $methodKey }}" required @checked($method === $methodKey)>
                            <span class="choice__label">{{ __('storefront.checkout.methods.'.$methodKey) }}</span>
                        </label>
                    @endforeach
                    @error('payment_method')<span class="error">{{ $message }}</span>@enderror
                </div>

                <p class="muted small" data-method-info="cod">{{ __('storefront.checkout.method_help.cod') }}</p>

                @if ($mobileMethods)
                    <div class="mobile-pay" data-method-info="{{ implode(' ', $mobileMethods) }}">
                        <p class="small">{{ __('storefront.checkout.method_help.mobile') }}</p>
                        @foreach ($mobileMethods as $m)
                            <p class="mobile-pay__number" data-method-info="{{ $m }}">
                                {{ __('storefront.checkout.send_to', ['method' => __('storefront.checkout.methods.'.$m), 'number' => \App\Support\CheckoutSettings::paymentNumber($m)]) }}
                            </p>
                        @endforeach
                        @if (\App\Support\CheckoutSettings::instructions())
                            <p class="small muted">{{ \App\Support\CheckoutSettings::instructions() }}</p>
                        @endif
                        <p class="muted small" data-no-js-only>{{ __('storefront.checkout.mobile_only') }}</p>
                        <label class="field">
                            <span>{{ __('storefront.checkout.payer_number') }}</span>
                            <input name="payer_number" value="{{ old('payer_number') }}" inputmode="tel" placeholder="{{ __('storefront.checkout.phone_hint') }}">
                            @error('payer_number')<span class="error">{{ $message }}</span>@enderror
                        </label>
                        <label class="field">
                            <span>{{ __('storefront.checkout.payment_reference') }}</span>
                            <input name="payment_reference" value="{{ old('payment_reference') }}" maxlength="30" autocapitalize="characters" spellcheck="false">
                            @error('payment_reference')<span class="error">{{ $message }}</span>@enderror
                        </label>
                    </div>
                @endif
            </fieldset>

            <fieldset class="panel">
                <legend class="sr-only">{{ __('storefront.checkout.note') }}</legend>
                <label class="field">
                    <span>{{ __('storefront.checkout.note') }}</span>
                    <textarea name="note" rows="2" maxlength="500">{{ old('note') }}</textarea>
                    @error('note')<span class="error">{{ $message }}</span>@enderror
                </label>
            </fieldset>
        </div>

        <aside class="checkout__summary panel" aria-labelledby="summary-heading">
            <h2 id="summary-heading" class="section-title">{{ __('storefront.checkout.summary') }}</h2>
            <ul class="summary-lines">
                @foreach ($lines as $line)
                    <li>
                        <span>{{ $line['variant']->product->name }} <span class="muted">· {{ $line['variant']->label }} × {{ $line['quantity'] }}</span></span>
                        <span>{{ Money::format($line['line_total']) }}</span>
                    </li>
                @endforeach
            </ul>
            <dl class="totals">
                <div><dt>{{ __('storefront.checkout.subtotal') }}</dt><dd>{{ Money::format($subtotal) }}</dd></div>
                <div><dt>{{ __('storefront.checkout.delivery_fee') }}</dt><dd data-delivery-fee>{{ Money::format($fees[$zone] ?? 0) }}</dd></div>
                <div class="totals__grand"><dt>{{ __('storefront.checkout.total') }}</dt><dd data-order-total>{{ Money::format($subtotal + ($fees[$zone] ?? 0)) }}</dd></div>
            </dl>
            <button type="submit" class="btn btn--primary btn--block" data-placing-text="{{ __('storefront.checkout.placing') }}">
                {{ __('storefront.checkout.place_order') }}
            </button>
        </aside>
    </form>
</div>
@endsection
