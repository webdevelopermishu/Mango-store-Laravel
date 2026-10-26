<?php

namespace App\Services;

use App\Exceptions\CartException;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;

/**
 * Session cart keyed by variant ID. Stores only IDs and quantities; prices and
 * stock are always read from the database, so a tampered request cannot set a price.
 */
class Cart
{
    private const SESSION_KEY = 'store.cart';

    private ?Collection $memo = null;

    /** @return array<int, int> variant_id => quantity */
    public function quantities(): array
    {
        return session(self::SESSION_KEY, []);
    }

    private function save(array $lines): void
    {
        session([self::SESSION_KEY => $lines]);
        $this->memo = null;
    }

    public function add(ProductVariant $variant, int $quantity): void
    {
        $existing = $this->quantities()[$variant->id] ?? 0;
        $this->setQuantity($variant, $existing + $quantity, $quantity < 1);
    }

    /** Sets a line's quantity; 0 removes it. */
    public function setQuantity(ProductVariant $variant, int $quantity, bool $forceInvalid = false): void
    {
        if ($quantity === 0 && ! $forceInvalid) {
            $this->remove($variant->id);

            return;
        }

        $max = (int) config('store.cart.max_qty_per_line');
        if ($forceInvalid || $quantity < 1 || $quantity > $max) {
            throw new CartException('storefront.cart.invalid_quantity');
        }

        $variant->loadMissing('product');
        if (! $variant->is_active || $variant->product?->status !== Product::STATUS_PUBLISHED) {
            throw new CartException('storefront.cart.unavailable');
        }
        if (! $variant->isInStock($quantity)) {
            throw new CartException('storefront.cart.insufficient_stock');
        }

        $lines = $this->quantities();
        $lines[$variant->id] = $quantity;
        $this->save($lines);
    }

    public function remove(int $variantId): void
    {
        $lines = $this->quantities();
        unset($lines[$variantId]);
        $this->save($lines);
    }

    public function clear(): void
    {
        $this->save([]);
    }

    /**
     * Valid cart lines with fresh prices. Lines whose product was unpublished or whose
     * variant was deactivated or deleted are removed from the session, so the customer
     * never gets stuck at checkout on an item they cannot see.
     *
     * @return Collection<int, array{variant: ProductVariant, quantity: int, line_total: int, in_stock: bool}>
     */
    public function lines(): Collection
    {
        if ($this->memo !== null) {
            return $this->memo;
        }

        $raw = $this->quantities();
        if ($raw === []) {
            return $this->memo = collect();
        }

        $variants = ProductVariant::with(['product.images'])
            ->whereIn('id', array_keys($raw))
            ->get()
            ->keyBy('id');

        $valid = [];
        $lines = collect();
        foreach ($raw as $id => $quantity) {
            $variant = $variants->get((int) $id);
            if (! $variant || ! $variant->is_active || $variant->product?->status !== Product::STATUS_PUBLISHED) {
                continue;
            }

            $valid[(int) $id] = $quantity;
            $lines->push([
                'variant' => $variant,
                'quantity' => $quantity,
                'line_total' => $variant->currentPricePaisa() * $quantity,
                'in_stock' => $variant->isInStock($quantity),
            ]);
        }

        if (count($valid) !== count($raw)) {
            session([self::SESSION_KEY => $valid]);
        }

        return $this->memo = $lines;
    }

    public function count(): int
    {
        return (int) $this->lines()->sum('quantity');
    }

    public function subtotal(): int
    {
        return (int) $this->lines()->sum('line_total');
    }
}
