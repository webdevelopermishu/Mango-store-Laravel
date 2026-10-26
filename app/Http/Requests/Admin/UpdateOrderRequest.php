<?php

namespace App\Http\Requests\Admin;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(Order::STATUSES)],
            'payment_status' => ['required', Rule::in(Order::PAYMENT_STATUSES)],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            /** @var Order $order */
            $order = $this->route('order');
            // Cancelling puts stock back; reopening would need it taken again, so cancelled is final.
            if ($order->isCancelled() && $this->input('status') !== 'cancelled') {
                $v->errors()->add('status', 'A cancelled order cannot be reopened. Ask the customer to place a new order.');
            }
        });
    }
}
