<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Category|null $current */
        $current = $this->route('category');

        return [
            'name_bn' => ['required', 'string', 'max:120'],
            'name_en' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'alpha_dash', 'max:150', Rule::unique('categories', 'slug')->ignore($current)],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'description_bn' => ['nullable', 'string', 'max:2000'],
            'description_en' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'banner' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072', 'dimensions:min_width=1200'],
            'seo_title_bn' => ['nullable', 'string', 'max:70'],
            'seo_title_en' => ['nullable', 'string', 'max:70'],
            'seo_description_bn' => ['nullable', 'string', 'max:160'],
            'seo_description_en' => ['nullable', 'string', 'max:160'],
            'is_published' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $parentId = $this->integer('parent_id') ?: null;
            if (! $parentId) {
                return;
            }

            /** @var Category|null $current */
            $current = $this->route('category');
            $parent = Category::find($parentId);

            // Nesting is limited to one level, which keeps breadcrumbs and cycles simple.
            if ($current && $parentId === $current->id) {
                $v->errors()->add('parent_id', 'A category cannot be its own parent.');
            } elseif ($parent?->parent_id) {
                $v->errors()->add('parent_id', 'Choose a top-level category as the parent.');
            } elseif ($current && $current->children()->exists()) {
                $v->errors()->add('parent_id', 'This category has subcategories, so it cannot become a subcategory.');
            }
        });
    }
}
