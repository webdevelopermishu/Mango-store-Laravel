<?php

namespace App\Support;

use App\Models\Order;
use App\Models\Setting;

/** Delivery fees and payment options, editable in Website Settings → Checkout. */
final class CheckoutSettings
{
    public const ZONES = ['inside_dhaka', 'outside_dhaka'];

    public static function deliveryFee(string $zone): int
    {
        $default = (int) config("store.checkout.delivery_fees.{$zone}", 0);

        return (int) Setting::read("checkout.fee_{$zone}_paisa", (string) $default);
    }

    /** @return array<string, int> zone => fee in paisa */
    public static function fees(): array
    {
        $fees = [];
        foreach (self::ZONES as $zone) {
            $fees[$zone] = self::deliveryFee($zone);
        }

        return $fees;
    }

    public static function paymentNumber(string $method): ?string
    {
        return Setting::read("checkout.{$method}_number");
    }

    /** Methods ticked in the admin. bKash/Nagad are offered only once their receiving number is set. */
    public static function enabledMethods(): array
    {
        $csv = Setting::read('checkout.methods', implode(',', config('store.checkout.default_methods')));
        $chosen = array_intersect(Order::PAYMENT_METHODS, explode(',', (string) $csv));

        $methods = array_values(array_filter($chosen, fn (string $m) => $m === 'cod' || filled(self::paymentNumber($m))));

        return $methods === [] ? ['cod'] : $methods;
    }

    public static function instructions(): ?string
    {
        return Setting::read('checkout.instructions_'.app()->getLocale());
    }
}
