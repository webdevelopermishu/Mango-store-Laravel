<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderRequest;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::withCount('items')
            ->when($request->filled('q'), function ($query) use ($request) {
                $like = '%'.addcslashes(trim($request->string('q')->toString()), '%_\\').'%';
                $query->where(fn ($q) => $q->where('order_number', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhere('customer_name', 'like', $like));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->when($request->filled('payment_status'), fn ($q) => $q->where('payment_status', $request->string('payment_status')->toString()))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load('items');

        return view('admin.orders.show', compact('order'));
    }

    public function update(UpdateOrderRequest $request, Order $order): RedirectResponse
    {
        DB::transaction(function () use ($request, $order) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $newStatus = $request->string('status')->toString();

            // Cancelling returns tracked stock. The lock plus the status check means it happens once.
            if ($newStatus === 'cancelled' && ! $locked->isCancelled()) {
                foreach ($locked->items as $item) {
                    if ($item->product_variant_id) {
                        ProductVariant::whereKey($item->product_variant_id)
                            ->whereNotNull('stock_quantity')
                            ->increment('stock_quantity', $item->quantity);
                    }
                }
            }

            $locked->update([
                'status' => $newStatus,
                'payment_status' => $request->string('payment_status')->toString(),
                'admin_note' => $request->input('admin_note'),
            ]);
        });

        return redirect()->route('admin.orders.show', $order)->with('status', 'Order updated.');
    }
}
