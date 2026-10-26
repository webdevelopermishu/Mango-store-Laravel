@extends('layouts.storefront')

@section('meta_title', \App\Support\Branding::metaTitle(__('storefront.order.title', ['number' => $order->order_number])))
@push('head')<meta name="robots" content="noindex">@endpush

@use('App\Support\Money')
@php
    $placed = $order->created_at->copy()->timezone(config('store.timezone'))->locale(app()->getLocale());
    $currentStep = array_search($order->status, \App\Models\Order::TIMELINE, true);
@endphp

@section('content')
<div class="container section order-page">
    @if (session('order_placed'))
        <div class="order-thanks" role="status">
            <svg viewBox="0 0 24 24" width="36" height="36" aria-hidden="true"><path fill="currentColor" d="m9 16.2-3.5-3.5L4 14.2l5 5 11-11-1.5-1.5z"/></svg>
            <div>
                <h1 class="page-title">{{ __('storefront.order.thanks') }}</h1>
                <p>{{ __('storefront.order.thanks_text') }}</p>
            </div>
        </div>
    @else
        <h1 class="page-title">{{ __('storefront.order.title', ['number' => $order->order_number]) }}</h1>
    @endif

    <div class="order-meta panel">
        <div><span class="muted small">{{ __('storefront.order.number') }}</span><strong class="order-number">{{ $order->order_number }}</strong></div>
        <div><span class="muted small">{{ __('storefront.order.placed_at') }}</span><strong>{{ $placed->translatedFormat('j F Y, g:i A') }}</strong></div>
        <div><span class="muted small">{{ __('storefront.checkout.total') }}</span><strong>{{ Money::format($order->total_paisa) }}</strong></div>
    </div>
    <p class="muted small">{{ __('storefront.order.save_link') }}</p>

    <section class="panel" aria-labelledby="status-heading">
        <h2 id="status-heading" class="section-title">{{ __('storefront.order.status_heading') }}</h2>
        @if ($order->isCancelled())
            <p class="flash flash--error">{{ __('storefront.order.status.cancelled') }} — {{ __('storefront.order.cancelled_text') }}</p>
        @else
            <ol class="timeline">
                @foreach (\App\Models\Order::TIMELINE as $i => $step)
                    <li class="timeline__step @if($currentStep !== false && $i <= $currentStep) is-done @endif"
                        @if($i === $currentStep) aria-current="step" @endif>
                        <span class="timeline__dot" aria-hidden="true"></span>
                        <span>{{ __('storefront.order.status.'.$step) }}</span>
                    </li>
                @endforeach
            </ol>
        @endif
    </section>

    <div class="order-grid">
        <section class="panel" aria-labelledby="items-heading">
            <h2 id="items-heading" class="section-title">{{ __('storefront.order.items') }}</h2>
            <ul class="summary-lines">
                @foreach ($order->items as $item)
                    <li>
                        <span>{{ $item->product_name }} <span class="muted">· {{ $item->variant_label }} × {{ $item->quantity }}</span></span>
                        <span>{{ Money::format($item->line_total_paisa) }}</span>
                    </li>
                @endforeach
            </ul>
            <dl class="totals">
                <div><dt>{{ __('storefront.checkout.subtotal') }}</dt><dd>{{ Money::format($order->subtotal_paisa) }}</dd></div>
                <div><dt>{{ __('storefront.checkout.delivery_fee') }}</dt><dd>{{ Money::format($order->delivery_fee_paisa) }}</dd></div>
                <div class="totals__grand"><dt>{{ __('storefront.checkout.total') }}</dt><dd>{{ Money::format($order->total_paisa) }}</dd></div>
            </dl>
        </section>

        <div class="order-side">
            <section class="panel" aria-labelledby="pay-heading">
                <h2 id="pay-heading" class="section-title">{{ __('storefront.order.payment_heading') }}</h2>
                <p><strong>{{ __('storefront.checkout.methods.'.$order->payment_method) }}</strong> · {{ __('storefront.order.payment_status.'.$order->payment_status) }}</p>
                @if (! $order->isCancelled())
                    @if ($order->payment_method === 'cod' && $order->payment_status === 'unpaid')
                        <p class="muted">{{ __('storefront.order.cod_text', ['amount' => Money::format($order->total_paisa)]) }}</p>
                    @elseif ($order->usesMobilePayment() && $order->payment_status === 'pending_verification')
                        <p class="muted">{{ __('storefront.order.verify_text', ['reference' => $order->payment_reference]) }}</p>
                    @endif
                @endif
            </section>

            <section class="panel" aria-labelledby="delivery-heading">
                <h2 id="delivery-heading" class="section-title">{{ __('storefront.order.delivery_heading') }}</h2>
                <p>{{ $order->customer_name }}<br>{{ $order->phone }}</p>
                <p>{!! nl2br(e($order->address)) !!}<br>{{ $order->district }} · {{ __('storefront.checkout.zones.'.$order->delivery_zone) }}</p>
                @if ($order->note)<p class="muted small">{{ $order->note }}</p>@endif
            </section>
        </div>
    </div>

    <p><a href="{{ route('order.track') }}">{{ __('storefront.order.track_cta') }}</a></p>
</div>
@endsection
