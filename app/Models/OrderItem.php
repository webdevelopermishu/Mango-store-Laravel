<?php

namespace App\Models;

use App\Models\Concerns\HasBilingualFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasBilingualFields;

    protected $fillable = [
        'order_id', 'product_id', 'product_variant_id', 'product_name_bn', 'product_name_en',
        'variant_label_bn', 'variant_label_en', 'sku', 'unit_price_paisa', 'quantity', 'line_total_paisa',
    ];

    protected $casts = [
        'unit_price_paisa' => 'integer',
        'quantity' => 'integer',
        'line_total_paisa' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getProductNameAttribute(): string
    {
        return $this->localized('product_name');
    }

    public function getVariantLabelAttribute(): string
    {
        return $this->localized('variant_label');
    }
}
