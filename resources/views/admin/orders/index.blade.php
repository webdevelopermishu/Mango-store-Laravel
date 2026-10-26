@extends('layouts.admin')
@section('title', 'Orders')

@section('content')
@use('App\Support\Money')
<h1>Orders</h1>

<form method="get" class="inline-filters">
    <input type="search" name="q" value="{{ request('q') }}" placeholder="Order number, phone or name" aria-label="Search orders">
    <select name="status" aria-label="Filter by order status">
        <option value="">All statuses</option>
        @foreach (\App\Models\Order::STATUSES as $s)
            <option value="{{ $s }}" @selected(request('status') === $s)>{{ __('storefront.order.status.'.$s, [], 'en') }}</option>
        @endforeach
    </select>
    <select name="payment_status" aria-label="Filter by payment status">
        <option value="">All payments</option>
        @foreach (\App\Models\Order::PAYMENT_STATUSES as $s)
            <option value="{{ $s }}" @selected(request('payment_status') === $s)>{{ __('storefront.order.payment_status.'.$s, [], 'en') }}</option>
        @endforeach
    </select>
    <button class="btn btn--ghost" type="submit">Filter</button>
</form>

<table class="admin-table">
    <thead><tr><th>Order</th><th>Placed</th><th>Customer</th><th>Items</th><th>Total</th><th>Payment</th><th>Status</th></tr></thead>
    <tbody>
    @forelse ($orders as $order)
        <tr>
            <td><a href="{{ route('admin.orders.show', $order) }}"><strong>{{ $order->order_number }}</strong></a></td>
            <td>{{ $order->created_at->copy()->timezone(config('store.timezone'))->format('d M Y, H:i') }}</td>
            <td>{{ $order->customer_name }}<br><span class="muted small">{{ $order->phone }}</span></td>
            <td>{{ $order->items_count }}</td>
            <td>{{ Money::format($order->total_paisa, 'en') }}</td>
            <td>{{ __('storefront.checkout.methods.'.$order->payment_method, [], 'en') }}<br>
                <span class="badge @if($order->payment_status !== 'paid') badge--muted @endif">{{ __('storefront.order.payment_status.'.$order->payment_status, [], 'en') }}</span></td>
            <td><span class="badge @if(in_array($order->status, ['cancelled','pending'])) badge--muted @endif">{{ __('storefront.order.status.'.$order->status, [], 'en') }}</span></td>
        </tr>
    @empty
        <tr><td colspan="7">No orders found.</td></tr>
    @endforelse
    </tbody>
</table>
<div class="pagination">{{ $orders->links('vendor.pagination.store') }}</div>
@endsection
