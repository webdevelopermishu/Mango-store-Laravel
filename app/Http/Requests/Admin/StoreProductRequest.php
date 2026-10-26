<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Product|null $current */
        $current = $this->route('product');

        return [
            'name_bn' => ['required', 'string', 'max:200'],
            'name_en' => ['required', 'string', 'max:200'],
            'slug' => ['required', 'alpha_dash', 'max:150', Rule::unique('products', 'slug')->ignore($current)],
            'sku' => ['nullable', 'string', 'max:60', Rule::unique('products', 'sku')->ignore($current)],
            'short_description_bn' => ['nullable', 'string', 'max:300'],
            'short_description_en' => ['nullable', 'string', 'max:300'],
            'description_bn' => ['nullable', 'string', 'max:10000'],
            'description_en' => ['nullable', 'string', 'max:10000'],
            'status' => ['required', Rule::in([Product::STATUS_DRAFT, Product::STATUS_PUBLISHED, Product::STATUS_OUT_OF_STOCK])],
            'unit' => ['required', Rule::in(['kg', 'g', 'piece', 'pack', 'bottle', 'jar'])],
            'is_seasonal' => ['sometimes', 'boolean'],
            'season_start_month' => ['nullable', 'integer', 'between:1,12', 'required_if:is_seasonal,1'],
            'season_end_month' => ['nullable', 'integer', 'between:1,12', 'required_if:is_seasonal,1'],
            'is_featured_home' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'seo_title_bn' => ['nullable', 'string', 'max:70'],
            'seo_title_en' => ['nullable', 'string', 'max:70'],
            'seo_description_bn' => ['nullable', 'string', 'max:160'],
            'seo_description_en' => ['nullable', 'string', 'max:160'],

            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],

            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'remove_image_ids' => ['nullable', 'array'],
            'remove_image_ids.*' => ['integer'],

            // Rows are validated individually in withValidator(); blank rows are ignored there.
            'variants' => ['required', 'array'],
            'variants.*.id' => ['nullable', 'integer'],
            'variants.*.label_bn' => ['nullable', 'string', 'max:80'],
            'variants.*.label_en' => ['nullable', 'string', 'max:80'],
            'variants.*.sku' => ['nullable', 'string', 'max:60'],
            'variants.*.weight_grams' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'variants.*.regular_price' => ['nullable', 'numeric', 'min:0.01', 'max:9999999'],
            'variants.*.sale_price' => ['nullable', 'numeric', 'min:0.01', 'max:9999999'],
            'variants.*.stock_quantity' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'variants.*.is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $filledRows = 0;

            foreach ((array) $this->input('variants', []) as $i => $row) {
                $row = is_array($row) ? $row : [];
                if (blank($row['label_bn'] ?? null) && blank($row['label_en'] ?? null) && blank($row['regular_price'] ?? null)) {
                    continue;
                }

                $filledRows++;
                foreach (['label_bn', 'label_en', 'regular_price'] as $field) {
                    if (blank($row[$field] ?? null)) {
                        $v->errors()->add("variants.$i.$field", 'This field is required for a filled variant row.');
                    }
                }

                $regular = (float) ($row['regular_price'] ?? 0);
                $sale = $row['sale_price'] ?? null;
                if (filled($sale) && $regular > 0 && (float) $sale >= $regular) {
                    $v->errors()->add("variants.$i.sale_price", 'Sale price must be lower than the regular price.');
                }
            }

            if ($filledRows === 0) {
                $v->errors()->add('variants', 'Add at least one variant with a name and price.');
            }
        });
    }
}
