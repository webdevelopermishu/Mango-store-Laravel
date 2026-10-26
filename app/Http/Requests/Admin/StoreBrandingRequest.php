<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBrandingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // SVG is deliberately not accepted: an uploaded SVG can contain script.
        return [
            'site_title' => ['required', 'string', 'max:120'],
            'tagline_bn' => ['nullable', 'string', 'max:160'],
            'tagline_en' => ['nullable', 'string', 'max:160'],
            'meta_title_template' => ['required', 'string', 'max:120', 'regex:/\{title\}/'],
            'meta_description_bn' => ['nullable', 'string', 'max:300'],
            'meta_description_en' => ['nullable', 'string', 'max:300'],

            // Recommended sizes are shown in the form; the minimums here enforce a usable floor.
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048', 'dimensions:min_width=200,min_height=60'],
            'mobile_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048', 'dimensions:min_width=120,min_height=120'],
            'footer_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048', 'dimensions:min_width=200,min_height=60'],
            'favicon' => ['nullable', 'file', 'mimes:png,ico,webp', 'max:512'],
            'og_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072', 'dimensions:min_width=600,min_height=315'],

            'remove' => ['nullable', 'array'],
            'remove.*' => ['string', Rule::in(['logo', 'mobile_logo', 'footer_logo', 'favicon', 'og_image'])],
        ];
    }
}
