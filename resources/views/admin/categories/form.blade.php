@extends('layouts.admin')
@section('title', $category->exists ? 'Edit category' : 'New category')

@section('content')
@php
    $isEdit = $category->exists;
    $action = $isEdit ? route('admin.categories.update', $category) : route('admin.categories.store');
    $selected = collect(old('product_ids', $selectedProducts))->map(fn ($v) => (int) $v)->all();
@endphp
<h1>{{ $isEdit ? 'Edit category' : 'New category' }}</h1>

<form method="post" action="{{ $action }}" enctype="multipart/form-data" class="admin-form">
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <fieldset>
        <legend>Names and slug</legend>
        <label class="field"><span>Name (বাংলা)</span><input name="name_bn" value="{{ old('name_bn', $category->name_bn) }}" required></label>
        <label class="field"><span>Name (English)</span><input name="name_en" value="{{ old('name_en', $category->name_en) }}" required></label>
        <label class="field"><span>Slug (English letters, numbers and dashes)</span><input name="slug" value="{{ old('slug', $category->slug) }}" required pattern="[A-Za-z0-9_-]+"></label>
        <label class="field"><span>Parent category (optional, one level only)</span>
            <select name="parent_id">
                <option value="">— Top level —</option>
                @foreach ($parents as $parent)
                    <option value="{{ $parent->id }}" @selected((int) old('parent_id', $category->parent_id) === $parent->id)>{{ $parent->name_en }}</option>
                @endforeach
            </select>
        </label>
    </fieldset>

    <fieldset>
        <legend>Descriptions</legend>
        <label class="field"><span>Description (বাংলা)</span><textarea name="description_bn" rows="3">{{ old('description_bn', $category->description_bn) }}</textarea></label>
        <label class="field"><span>Description (English)</span><textarea name="description_en" rows="3">{{ old('description_en', $category->description_en) }}</textarea></label>
    </fieldset>

    <fieldset>
        <legend>Images</legend>
        <label class="field"><span>Category image (square, 800 × 800 px recommended)</span><input type="file" name="image" accept="image/*">
            @if ($category->image_url)<img src="{{ $category->image_url }}" alt="" width="120" height="120">@endif</label>
        <label class="field"><span>Banner (1200 px wide minimum)</span><input type="file" name="banner" accept="image/*">
            @if ($category->banner_url)<img src="{{ $category->banner_url }}" alt="" width="240">@endif</label>
    </fieldset>

    <fieldset>
        <legend>SEO</legend>
        <label class="field"><span>SEO title (বাংলা)</span><input name="seo_title_bn" value="{{ old('seo_title_bn', $category->seo_title_bn) }}" maxlength="70"></label>
        <label class="field"><span>SEO title (English)</span><input name="seo_title_en" value="{{ old('seo_title_en', $category->seo_title_en) }}" maxlength="70"></label>
        <label class="field"><span>SEO description (বাংলা)</span><textarea name="seo_description_bn" rows="2" maxlength="160">{{ old('seo_description_bn', $category->seo_description_bn) }}</textarea></label>
        <label class="field"><span>SEO description (English)</span><textarea name="seo_description_en" rows="2" maxlength="160">{{ old('seo_description_en', $category->seo_description_en) }}</textarea></label>
    </fieldset>

    <fieldset>
        <legend>Visibility and order</legend>
        <label class="checkbox"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $category->is_published))> Published</label>
        <label class="checkbox"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $category->is_featured))> Show on the homepage</label>
        <label class="field"><span>Sort order</span><input type="number" min="0" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}" class="narrow"></label>
    </fieldset>

    <fieldset>
        <legend>Products in this category</legend>
        <select name="product_ids[]" multiple size="10" class="multi">
            @foreach ($products as $product)
                <option value="{{ $product->id }}" @selected(in_array($product->id, $selected, true))>{{ $product->name_en }} ({{ $product->sku ?? $product->slug }})</option>
            @endforeach
        </select>
        <p class="muted small">Hold Ctrl or Cmd to select several. Hidden categories keep their products.</p>
    </fieldset>

    <button class="btn btn--primary" type="submit">Save category</button>
</form>
@endsection
