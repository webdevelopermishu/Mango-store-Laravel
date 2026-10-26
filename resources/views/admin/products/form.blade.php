@extends('layouts.admin')
@section('title', $product->exists ? 'Edit product' : 'New product')

@section('content')
@php
    $isEdit = $product->exists;
    $action = $isEdit ? route('admin.products.update', $product) : route('admin.products.store');
    $chosen = collect(old('category_ids', $selectedCategories))->map(fn ($v) => (int) $v)->all();
@endphp
<h1>{{ $isEdit ? 'Edit product' : 'New product' }}</h1>

<form method="post" action="{{ $action }}" enctype="multipart/form-data" class="admin-form">
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <fieldset>
        <legend>Names, slug and SKU</legend>
        <label class="field"><span>Name (বাংলা)</span><input name="name_bn" value="{{ old('name_bn', $product->name_bn) }}" required></label>
        <label class="field"><span>Name (English)</span><input name="name_en" value="{{ old('name_en', $product->name_en) }}" required></label>
        <label class="field"><span>Slug (English letters, numbers and dashes)</span><input name="slug" value="{{ old('slug', $product->slug) }}" required pattern="[A-Za-z0-9_-]+"></label>
        <label class="field"><span>Product SKU (optional)</span><input name="sku" value="{{ old('sku', $product->sku) }}" maxlength="60"></label>
    </fieldset>

    <fieldset>
        <legend>Descriptions</legend>
        <label class="field"><span>Short description (বাংলা)</span><input name="short_description_bn" value="{{ old('short_description_bn', $product->short_description_bn) }}" maxlength="300"></label>
        <label class="field"><span>Short description (English)</span><input name="short_description_en" value="{{ old('short_description_en', $product->short_description_en) }}" maxlength="300"></label>
        <label class="field"><span>Full description (বাংলা)</span><textarea name="description_bn" rows="5">{{ old('description_bn', $product->description_bn) }}</textarea></label>
        <label class="field"><span>Full description (English)</span><textarea name="description_en" rows="5">{{ old('description_en', $product->description_en) }}</textarea></label>
    </fieldset>

    <fieldset>
        <legend>Publishing</legend>
        <label class="field"><span>Status</span>
            <select name="status" required>
                @foreach (['draft' => 'Draft (hidden)', 'published' => 'Published', 'out_of_stock' => 'Published, out of stock'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $product->status) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="field"><span>Unit</span>
            <select name="unit" required>
                @foreach (['piece' => 'Piece', 'kg' => 'Kilogram', 'g' => 'Gram', 'pack' => 'Pack', 'bottle' => 'Bottle', 'jar' => 'Jar'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('unit', $product->unit) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="checkbox"><input type="checkbox" name="is_featured_home" value="1" @checked(old('is_featured_home', $product->is_featured_home))> Show on the homepage</label>
        <label class="field"><span>Sort order</span><input type="number" min="0" name="sort_order" value="{{ old('sort_order', $product->sort_order) }}" class="narrow"></label>
    </fieldset>

    <fieldset>
        <legend>Seasonality</legend>
        <label class="checkbox"><input type="checkbox" name="is_seasonal" value="1" @checked(old('is_seasonal', $product->is_seasonal))> This product is seasonal</label>
        <div class="row">
            <label class="field"><span>Season starts (month)</span>
                <select name="season_start_month"><option value="">—</option>
                    @for ($m = 1; $m <= 12; $m++)<option value="{{ $m }}" @selected((int) old('season_start_month', $product->season_start_month) === $m)>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>@endfor
                </select></label>
            <label class="field"><span>Season ends (month)</span>
                <select name="season_end_month"><option value="">—</option>
                    @for ($m = 1; $m <= 12; $m++)<option value="{{ $m }}" @selected((int) old('season_end_month', $product->season_end_month) === $m)>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>@endfor
                </select></label>
        </div>
    </fieldset>

    <fieldset>
        <legend>Categories</legend>
        <select name="category_ids[]" multiple size="8" class="multi">
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(in_array($category->id, $chosen, true))>{{ $category->name_en }}{{ $category->parent_id ? ' (sub)' : '' }}</option>
            @endforeach
        </select>
        <p class="muted small">Hold Ctrl or Cmd to select several.</p>
    </fieldset>

    <fieldset>
        <legend>Images</legend>
        @if ($isEdit && $product->images->isNotEmpty())
            <ul class="image-picker">
                @foreach ($product->images as $image)
                    <li>
                        <img src="{{ $image->url }}" alt="" width="120" height="120">
                        <label class="checkbox"><input type="checkbox" name="remove_image_ids[]" value="{{ $image->id }}"> Remove</label>
                    </li>
                @endforeach
            </ul>
        @endif
        <label class="field"><span>Add images (JPG, PNG or WebP, max 3 MB each, up to 10)</span>
            <input type="file" name="images[]" accept="image/*" multiple></label>
    </fieldset>

    <fieldset>
        <legend>Sizes, prices and stock</legend>
        <p class="muted small">Prices are in BDT (৳). Leave a sale price empty for no discount. Stock left empty is not tracked.</p>
        <table class="admin-table variant-table">
            <thead><tr><th>Label (বাংলা)</th><th>Label (English)</th><th>SKU</th><th>Weight (g)</th><th>Regular price ৳</th><th>Sale price ৳</th><th>Stock</th><th>Active</th></tr></thead>
            <tbody>
            @foreach ($variantRows as $i => $row)
                <tr>
                    <td>
                        <input type="hidden" name="variants[{{ $i }}][id]" value="{{ $row->id }}">
                        <input name="variants[{{ $i }}][label_bn]" value="{{ old("variants.$i.label_bn", $row->label_bn) }}" aria-label="Label Bangla row {{ $i + 1 }}">
                    </td>
                    <td><input name="variants[{{ $i }}][label_en]" value="{{ old("variants.$i.label_en", $row->label_en) }}" aria-label="Label English row {{ $i + 1 }}"></td>
                    <td><input name="variants[{{ $i }}][sku]" value="{{ old("variants.$i.sku", $row->sku) }}"></td>
                    <td><input type="number" min="0" name="variants[{{ $i }}][weight_grams]" value="{{ old("variants.$i.weight_grams", $row->weight_grams) }}" class="narrow"></td>
                    <td><input type="number" step="0.01" min="0.01" name="variants[{{ $i }}][regular_price]" value="{{ old("variants.$i.regular_price", $row->regular_price_paisa !== null ? number_format($row->regular_price_paisa / 100, 2, '.', '') : '') }}" class="narrow"></td>
                    <td><input type="number" step="0.01" min="0.01" name="variants[{{ $i }}][sale_price]" value="{{ old("variants.$i.sale_price", $row->sale_price_paisa !== null ? number_format($row->sale_price_paisa / 100, 2, '.', '') : '') }}" class="narrow"></td>
                    <td><input type="number" min="0" name="variants[{{ $i }}][stock_quantity]" value="{{ old("variants.$i.stock_quantity", $row->stock_quantity) }}" class="narrow"></td>
                    <td><input type="checkbox" name="variants[{{ $i }}][is_active]" value="1" @checked(old("variants.$i.is_active", $row->exists ? $row->is_active : true))></td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <p class="muted small">Two empty rows are provided. Fill a row to add a variant; remove a variant by clearing its label and price.</p>
    </fieldset>

    <fieldset>
        <legend>SEO</legend>
        <label class="field"><span>SEO title (বাংলা)</span><input name="seo_title_bn" value="{{ old('seo_title_bn', $product->seo_title_bn) }}" maxlength="70"></label>
        <label class="field"><span>SEO title (English)</span><input name="seo_title_en" value="{{ old('seo_title_en', $product->seo_title_en) }}" maxlength="70"></label>
        <label class="field"><span>SEO description (বাংলা)</span><textarea name="seo_description_bn" rows="2" maxlength="160">{{ old('seo_description_bn', $product->seo_description_bn) }}</textarea></label>
        <label class="field"><span>SEO description (English)</span><textarea name="seo_description_en" rows="2" maxlength="160">{{ old('seo_description_en', $product->seo_description_en) }}</textarea></label>
    </fieldset>

    <button class="btn btn--primary" type="submit">Save product</button>
</form>
@endsection
