<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUSES = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
    /** Steps shown in the customer's progress timeline (cancelled is shown separately). */
    public const TIMELINE = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
    public const PAYMENT_METHODS = ['cod', 'bkash', 'nagad'];
    public const MOBILE_METHODS = ['bkash', 'nagad'];
    public const PAYMENT_STATUSES = ['unpaid', 'pending_verification', 'paid', 'refunded'];

    protected $fillable = [
        'order_number', 'access_token', 'locale', 'customer_name', 'phone', 'email', 'address',
        'district', 'delivery_zone', 'note', 'subtotal_paisa', 'delivery_fee_paisa', 'total_paisa',
        'payment_method', 'payment_status', 'payer_number', 'payment_reference', 'status', 'admin_note',
    ];

    protected $hidden = ['access_token'];

    protected $casts = [
        'subtotal_paisa' => 'integer',
        'delivery_fee_paisa' => 'integer',
        'total_paisa' => 'integer',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public static function numberFor(int $id): string
    {
        return 'BD'.str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function usesMobilePayment(): bool
    {
        return in_array($this->payment_method, self::MOBILE_METHODS, true);
    }

    /** Private link to the customer's order page, in the current language. */
    public function publicUrl(): string
    {
        return route('order.show', [
            'locale' => app()->getLocale(),
            'order' => $this->order_number,
            'key' => $this->access_token,
        ]);
    }
}
