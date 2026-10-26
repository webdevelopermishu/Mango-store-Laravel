<?php

namespace App\Http\Controllers;

use App\Exceptions\CheckoutException;
use App\Http\Requests\CheckoutRequest;
use App\Services\Cart;
use App\Services\PlaceOrder;
use App\Support\CheckoutSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function create(Cart $cart)
    {
        $lines = $cart->lines();
        if ($lines->isEmpty()) {
            return redirect()->route('cart.index');
        }

        // One token per checkout, reused across tabs and validation errors, consumed on success.
        $token = session('checkout.token');
        if (! is_string($token) || strlen($token) !== 40) {
            $token = Str::random(40);
            session(['checkout.token' => $token]);
        }

        return view('storefront.checkout', [
            'lines' => $lines,
            'subtotal' => $cart->subtotal(),
            'fees' => CheckoutSettings::fees(),
            'methods' => CheckoutSettings::enabledMethods(),
            'token' => $token,
        ]);
    }

    public function store(CheckoutRequest $request, PlaceOrder $placeOrder): RedirectResponse
    {
        $token = $request->string('checkout_token')->toString();

        if (! hash_equals((string) session('checkout.token'), $token)) {
            return $this->alreadyPlacedOrExpired();
        }
        // Atomic guard: a double-click or a resent form with the same token creates one order only.
        if (! Cache::add("checkout:{$token}", true, 600)) {
            return $this->alreadyPlacedOrExpired();
        }

        try {
            $order = $placeOrder->handle($request->validated(), app()->getLocale());
        } catch (CheckoutException $e) {
            Cache::forget("checkout:{$token}");

            return redirect()->route('checkout.create')
                ->withErrors(['checkout' => __($e->getMessage(), $e->replace)])
                ->withInput();
        }

        session()->forget('checkout.token');
        session(['checkout.last_order' => ['number' => $order->order_number, 'key' => $order->access_token]]);

        return redirect()->to($order->publicUrl())->with('order_placed', true);
    }

    private function alreadyPlacedOrExpired(): RedirectResponse
    {
        $last = session('checkout.last_order');
        if (is_array($last) && isset($last['number'], $last['key'])) {
            return redirect()->route('order.show', ['order' => $last['number'], 'key' => $last['key']]);
        }

        return redirect()->route('checkout.create')
            ->withErrors(['checkout' => __('storefront.checkout.errors.expired')]);
    }
}
