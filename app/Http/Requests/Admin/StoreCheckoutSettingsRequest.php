<?php

namespace App\Http\Requests\Admin;

use App\Models\Order;
use App\Support\Phone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCheckoutSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'bkash_number' => Phone::normalize($this->input('bkash_number')),
            'nagad_number' => Phone::normalize($this->input('nagad_number')),
        ]);
    }

    public function rules(): array
    {
        return [
            'fee_inside_dhaka' => ['required', 'numeric', 'min:0', 'max:100000'],
            'fee_outside_dhaka' => ['required', 'numeric', 'min:0', 'max:100000'],
            'methods' => ['required', 'array', 'min:1'],
            'methods.*' => [Rule::in(Order::PAYMENT_METHODS)],
            'bkash_number' => ['nullable', 'regex:'.Phone::PATTERN],
            'nagad_number' => ['nullable', 'regex:'.Phone::PATTERN],
            'instructions_bn' => ['nullable', 'string', 'max:1000'],
            'instructions_en' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            foreach (Order::MOBILE_METHODS as $method) {
                if (in_array($method, (array) $this->input('methods', []), true) && blank($this->input("{$method}_number"))) {
                    $v->errors()->add("{$method}_number", 'Enter the receiving number to offer '.ucfirst($method).'.');
                }
            }
        });
    }
}
