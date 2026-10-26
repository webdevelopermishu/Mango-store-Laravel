<?php

namespace App\Http\Requests;

use App\Support\CheckoutSettings;
use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $reference = $this->input('payment_reference');

        $this->merge([
            'phone' => Phone::normalize($this->input('phone')),
            'payer_number' => Phone::normalize($this->input('payer_number')),
            'payment_reference' => is_string($reference) && trim($reference) !== '' ? strtoupper(trim($reference)) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'regex:'.Phone::PATTERN],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['required', 'string', 'max:500'],
            'district' => ['required', 'string', 'max:120'],
            'delivery_zone' => ['required', Rule::in(CheckoutSettings::ZONES)],
            'payment_method' => ['required', Rule::in(CheckoutSettings::enabledMethods())],
            'payer_number' => ['nullable', 'required_if:payment_method,bkash,nagad', 'regex:'.Phone::PATTERN],
            'payment_reference' => ['nullable', 'required_if:payment_method,bkash,nagad', 'alpha_num', 'max:30'],
            'note' => ['nullable', 'string', 'max:500'],
            'expected_subtotal' => ['required', 'integer', 'min:0'],
            'checkout_token' => ['required', 'string', 'size:40'],
        ];
    }

    public function messages(): array
    {
        $e = fn (string $key) => __('storefront.checkout.errors.'.$key);

        return [
            'name.required' => $e('name_required'),
            'name.max' => $e('too_long'),
            'phone.required' => $e('phone_required'),
            'phone.regex' => $e('phone_invalid'),
            'email.email' => $e('email_invalid'),
            'email.max' => $e('too_long'),
            'address.required' => $e('address_required'),
            'address.max' => $e('too_long'),
            'district.required' => $e('district_required'),
            'district.max' => $e('too_long'),
            'delivery_zone.required' => $e('zone_required'),
            'delivery_zone.in' => $e('zone_required'),
            'payment_method.required' => $e('method_required'),
            'payment_method.in' => $e('method_required'),
            'payer_number.required_if' => $e('payer_required'),
            'payer_number.regex' => $e('phone_invalid'),
            'payment_reference.required_if' => $e('reference_required'),
            'payment_reference.alpha_num' => $e('reference_invalid'),
            'payment_reference.max' => $e('too_long'),
            'note.max' => $e('too_long'),
            'expected_subtotal.*' => $e('expired'),
            'checkout_token.*' => $e('expired'),
        ];
    }
}
