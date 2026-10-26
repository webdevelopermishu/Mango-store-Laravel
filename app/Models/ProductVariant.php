<?php

namespace App\Models;

use App\Models\Concerns\HasBilingualFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasBilingualFields;

    protected $fillable = [
        'product_id', 'sku', 'label_bn', 'label_en', 'weight_grams',
        'regular_price_paisa', 'sale_price_paisa', 'stock_quantity', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'regular_price_paisa' => 'integer',
        'sale_price_paisa' => 'integer',
        'stock_quantity' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getLabelAttribute(): string
    {
        return $this->localized('label');
    }

    public function currentPricePaisa(): int
    {
        return $this->sale_price_paisa ?? $this->regular_price_paisa;
    }

    public function isOnSale(): bool
    {
        return $this->sale_price_paisa !== null && $this->sale_price_paisa < $this->regular_price_paisa;
    }

    /** Untracked stock (null) is always available; tracked stock must cover the requested quantity. */
    public function isInStock(int $quantity = 1): bool
    {
        return $this->is_active && ($this->stock_quantity === null || $this->stock_quantity >= $quantity);
    }
}
