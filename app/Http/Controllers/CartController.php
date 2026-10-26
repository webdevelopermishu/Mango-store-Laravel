<?php

namespace App\Http\Controllers;

use App\Exceptions\CartException;
use App\Http\Requests\AddToCartRequest;
use App\Models\ProductVariant;
use App\Services\Cart;
use App\Support\Money;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CartController extends Controller
{
    public function index(Cart $cart)
    {
        return view('storefront.cart', [
            'lines' => $cart->lines(),
            'subtotal' => $cart->subtotal(),
        ]);
    }

    public function store(AddToCartRequest $request, Cart $cart): JsonResponse|RedirectResponse
    {
        $variant = ProductVariant::with(['product.images'])->findOrFail($request->integer('variant_id'));
        $quantity = $request->integer('quantity');
        $requestId = (string) $request->input('request_id', '');

        try {
            // Cache::add returns false if this request ID was already processed: a repeat is a no-op.
            if ($requestId === '' || Cache::add("cart-request:{$requestId}", true, 120)) {
                $cart->add($variant, $quantity);
            }
        } catch (CartException $e) {
            if ($requestId !== '') {
                Cache::forget("cart-request:{$requestId}");
            }
            $message = __($e->getMessage());

            return $request->expectsJson()
                ? response()->json(['ok' => false, 'message' => $message], 422)
                : back()->withErrors(['cart' => $message])->withInput();
        }

        $message = __('storefront.cart.added_message', ['product' => $variant->product->name]);
        $payload = [
            'ok' => true,
            'message' => $message,
            'count' => $cart->count(),
            'subtotal' => $cart->subtotal(),
            'subtotal_text' => Money::format($cart->subtotal()),
            'item' => [
                'name' => $variant->product->name,
                'variant' => $variant->label,
                'quantity' => $quantity,
                'image' => $variant->product->images->first()?->url,
            ],
        ];

        return $request->expectsJson()
            ? response()->json($payload)
            : redirect()->back()->with('status', $message);
    }

    public function update(Request $request, ProductVariant $variant, Cart $cart): RedirectResponse
    {
        $data = $request->validate(
            ['quantity' => ['required', 'integer', 'min:0', 'max:'.config('store.cart.max_qty_per_line')]],
            ['quantity.*' => __('storefront.cart.invalid_quantity')]
        );

        try {
            $cart->setQuantity($variant, (int) $data['quantity']);
        } catch (CartException $e) {
            return redirect()->route('cart.index')->withErrors(['cart' => __($e->getMessage())]);
        }

        return redirect()->route('cart.index')->with('status', __('storefront.cart.updated'));
    }

    public function destroy(ProductVariant $variant, Cart $cart): RedirectResponse
    {
        $cart->remove($variant->id);

        return redirect()->route('cart.index')->with('status', __('storefront.cart.removed'));
    }
}
