<?php

namespace App\Services;

use App\Exceptions\CheckoutException;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Support\CheckoutSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Turns the cart into an order. Variant rows are locked while stock is checked and
 * reduced, so two customers cannot buy the last unit at the same time.
 */
class PlaceOrder
{
    public function __construct(private Cart $cart)
    {
    }

    /** @throws CheckoutException */
    public function handle(array $data, string $locale): Order
    {
        $this->cart->lines(); // drops lines that are no longer sellable
        $quantities = $this->cart->quantities();
        if ($quantities === []) {
            throw new CheckoutException('storefront.checkout.errors.empty_cart');
        }

        $zone = $data['delivery_zone'];
        $fee = CheckoutSettings::deliveryFee($zone);
        $isMobile = in_array($data['payment_method'], Order::MOBILE_METHODS, true);

        $order = DB::transaction(function () use ($quantities, $data, $locale, $zone, $fee, $isMobile) {
            $variants = ProductVariant::with('product')
                ->whereIn('id', array_keys($quantities))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $items = [];
            $subtotal = 0;
            foreach ($quantities as $id => $quantity) {
                $variant = $variants->get((int) $id);
                $name = $variant?->product?->name ?? '';

                if (! $variant || ! $variant->is_active || $variant->product?->status !== Product::STATUS_PUBLISHED) {
                    throw new CheckoutException('storefront.checkout.errors.item_unavailable', ['product' => $name]);
                }
                if (! $variant->isInStock($quantity)) {
                    throw new CheckoutException('storefront.checkout.errors.insufficient_stock', ['product' => $name]);
                }

                $price = $variant->currentPricePaisa();
                $subtotal += $price * $quantity;
                $items[] = [
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'product_name_bn' => $variant->product->name_bn,
                    'product_name_en' => $variant->product->name_en,
                    'variant_label_bn' => $variant->label_bn,
                    'variant_label_en' => $variant->label_en,
                    'sku' => $variant->sku ?? $variant->product->sku,
                    'unit_price_paisa' => $price,
                    'quantity' => $quantity,
                    'line_total_paisa' => $price * $quantity,
                ];
            }

            // The customer saw (and may already have sent by bKash) a total based on this
            // subtotal. If prices changed since the page loaded, stop and show the new total.
            if ($subtotal !== (int) $data['expected_subtotal']) {
                throw new CheckoutException('storefront.checkout.errors.total_changed');
            }

            foreach ($items as $item) {
                ProductVariant::whereKey($item['product_variant_id'])
                    ->whereNotNull('stock_quantity')
                    ->decrement('stock_quantity', $item['quantity']);
            }

            $order = Order::create([
                'access_token' => Str::random(40),
                'locale' => $locale,
                'customer_name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'address' => $data['address'],
                'district' => $data['district'],
                'delivery_zone' => $zone,
                'note' => $data['note'] ?? null,
                'subtotal_paisa' => $subtotal,
                'delivery_fee_paisa' => $fee,
                'total_paisa' => $subtotal + $fee,
                'payment_method' => $data['payment_method'],
                'payment_status' => $isMobile ? 'pending_verification' : 'unpaid',
                'payer_number' => $isMobile ? $data['payer_number'] : null,
                'payment_reference' => $isMobile ? $data['payment_reference'] : null,
                'status' => 'pending',
            ]);
            $order->update(['order_number' => Order::numberFor($order->id)]);
            $order->items()->createMany($items);

            return $order;
        });

        $this->cart->clear();

        return $order;
    }
}
