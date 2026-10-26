<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\Phone;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /** Customer order page. The secret key in the link is required; without it the order does not exist. */
    public function show(Request $request, Order $order)
    {
        $key = $request->query('key');
        abort_unless(is_string($key) && hash_equals($order->access_token, $key), 404);

        $order->load('items');

        return view('storefront.order', compact('order'));
    }

    /** Lookup by order number + the phone number used at checkout. */
    public function track(Request $request)
    {
        $notFound = false;

        if ($request->filled('order_number') || $request->filled('phone')) {
            $request->merge([
                'order_number' => strtoupper(trim((string) $request->query('order_number'))),
                'phone' => Phone::normalize($request->query('phone')),
            ]);

            $request->validate([
                'order_number' => ['required', 'string', 'max:20'],
                'phone' => ['required', 'regex:'.Phone::PATTERN],
            ], [
                'order_number.required' => __('storefront.track.errors.order_required'),
                'order_number.max' => __('storefront.track.not_found'),
                'phone.required' => __('storefront.checkout.errors.phone_required'),
                'phone.regex' => __('storefront.checkout.errors.phone_invalid'),
            ]);

            $order = Order::where('order_number', $request->input('order_number'))
                ->where('phone', $request->input('phone'))
                ->first();

            if ($order) {
                return redirect()->to($order->publicUrl());
            }
            $notFound = true;
        }

        return view('storefront.track', compact('notFound'));
    }
}
