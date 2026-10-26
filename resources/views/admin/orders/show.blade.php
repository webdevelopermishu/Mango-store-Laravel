@extends('layouts.admin')
@section('title', 'Order '.$order->order_number)

@section('content')
@use('App\Support\Money')
<p><a href="{{ route('admin.orders.index') }}">← All orders</a></p>
<h1>Order {{ $order->order_number }}</h1>
<p class="muted">Placed {{ $order->created_at->copy()->timezone(config('store.timezone'))->format('d M Y, H:i') }}
    · Customer language: {{ $order->locale === 'bn' ? 'Bangla' : 'English' }}
    · <a href="{{ route('order.show', ['locale' => $order->locale, 'order' => $order->order_number, 'key' => $order->access_token]) }}" target="_blank" rel="noopener">Customer's order page</a></p>

<div class="admin-columns">
    <section class="admin-card">
        <h2>Customer</h2>
        <p><strong>{{ $order->customer_name }}</strong><br>
            <a href="tel:{{ $order->phone }}">{{ $order->phone }}</a>
            @if ($order->email)<br><a href="mailto:{{ $order->email }}">{{ $order->email }}</a>@endif</p>
        <p>{!! nl2br(e($order->address)) !!}<br>{{ $order->district }} · {{ __('storefront.checkout.zones.'.$order->delivery_zone, [], 'en') }}</p>
        @if ($order->note)<p><strong>Customer note:</strong> {{ $order->note }}</p>@endif
    </section>

    <section class="admin-card">
        <h2>Payment</h2>
        <p><strong>{{ __('storefront.checkout.methods.'.$order->payment_method, [], 'en') }}</strong>
            · {{ __('storefront.order.payment_status.'.$order->payment_status, [], 'en') }}</p>
        @if ($order->usesMobilePayment())
            <p>Paid from: <strong>{{ $order->payer_number }}</strong><br>
                Transaction ID: <strong><code>{{ $order->payment_reference }}</code></strong></p>
            <p class="muted small">Check this transaction in your {{ ucfirst($order->payment_method) }} account before marking the order as paid.</p>
        @endif
    </section>
</div>

<section class="admin-card">
    <h2>Items</h2>
    <table class="admin-table">
        <thead><tr><th>Product</th><th>Variant</th><th>SKU</th><th>Unit price</th><th>Qty</th><th>Line total</th></tr></thead>
        <tbody>
        @foreach ($order->items as $item)
            <tr>
                <td>{{ $item->product_name_en }}<br><span class="muted small" lang="bn">{{ $item->product_name_bn }}</span></td>
                <td>{{ $item->variant_label_en }}</td>
                <td>{{ $item->sku ?? '—' }}</td>
                <td>{{ Money::format($item->unit_price_paisa, 'en') }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ Money::format($item->line_total_paisa, 'en') }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr><td colspan="5">Subtotal</td><td>{{ Money::format($order->subtotal_paisa, 'en') }}</td></tr>
            <tr><td colspan="5">Delivery</td><td>{{ Money::format($order->delivery_fee_paisa, 'en') }}</td></tr>
            <tr><td colspan="5"><strong>Total</strong></td><td><strong>{{ Money::format($order->total_paisa, 'en') }}</strong></td></tr>
        </tfoot>
    </table>
</section>

<form method="post" action="{{ route('admin.orders.update', $order) }}" class="admin-form">
    @csrf
    @method('PUT')
    <fieldset>
        <legend>Update order</legend>
        <label class="field"><span>Order status</span>
            <select name="status">
                @foreach (\App\Models\Order::STATUSES as $s)
                    <option value="{{ $s }}" @selected(old('status', $order->status) === $s) @disabled($order->isCancelled() && $s !== 'cancelled')>{{ __('storefront.order.status.'.$s, [], 'en') }}</option>
                @endforeach
            </select>
        </label>
        <p class="muted small">Cancelling returns tracked stock to the product and cannot be undone.</p>
        <label class="field"><span>Payment status</span>
            <select name="payment_status">
                @foreach (\App\Models\Order::PAYMENT_STATUSES as $s)
                    <option value="{{ $s }}" @selected(old('payment_status', $order->payment_status) === $s)>{{ __('storefront.order.payment_status.'.$s, [], 'en') }}</option>
                @endforeach
            </select>
        </label>
        <label class="field"><span>Internal note (not shown to the customer)</span>
            <textarea name="admin_note" rows="3" maxlength="2000">{{ old('admin_note', $order->admin_note) }}</textarea>
        </label>
        <button class="btn btn--primary" type="submit">Save changes</button>
    </fieldset>
</form>
@endsection
