<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:'.config('store.cart.max_qty_per_line')],
            // One ID per button click, so a retried request cannot add the item twice.
            'request_id' => ['nullable', 'string', 'max:64'],
        ];
    }

    public function messages(): array
    {
        return [
            'variant_id.required' => __('storefront.cart.unavailable'),
            'variant_id.exists' => __('storefront.cart.unavailable'),
            'quantity.required' => __('storefront.cart.invalid_quantity'),
            'quantity.integer' => __('storefront.cart.invalid_quantity'),
            'quantity.min' => __('storefront.cart.invalid_quantity'),
            'quantity.max' => __('storefront.cart.invalid_quantity'),
        ];
    }

    /** AJAX callers get our translated message instead of Laravel's default English text. */
    protected function failedValidation(Validator $validator): void
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(response()->json([
                'ok' => false,
                'message' => $validator->errors()->first(),
            ], 422));
        }

        parent::failedValidation($validator);
    }
}
