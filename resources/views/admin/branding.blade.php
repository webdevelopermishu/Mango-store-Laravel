@extends('layouts.admin')
@section('title', 'Website settings · Branding')

@section('content')
<h1>Website settings → Branding</h1>
<p class="muted">Changes apply across the storefront. Leave an upload empty to keep the current image.</p>

<form method="post" action="{{ route('admin.branding.update') }}" enctype="multipart/form-data" class="admin-form">
    @csrf
    @method('PUT')

    <fieldset>
        <legend>Identity</legend>
        <label class="field"><span>Website title</span>
            <input name="site_title" value="{{ old('site_title', $values['site_title']) }}" required maxlength="120"></label>
        <label class="field"><span>Tagline (বাংলা)</span>
            <input name="tagline_bn" value="{{ old('tagline_bn', $values['tagline_bn']) }}" maxlength="160"></label>
        <label class="field"><span>Tagline (English)</span>
            <input name="tagline_en" value="{{ old('tagline_en', $values['tagline_en']) }}" maxlength="160"></label>
    </fieldset>

    <fieldset>
        <legend>Images</legend>
        @foreach ([
            'logo' => ['Main logo', '200 × 60 px minimum, PNG/JPG/WebP, max 2 MB'],
            'mobile_logo' => ['Mobile logo', '120 × 120 px minimum; falls back to the main logo'],
            'footer_logo' => ['Footer logo', '200 × 60 px minimum; falls back to the main logo'],
            'favicon' => ['Favicon', 'PNG, ICO or WebP, 512 × 512 px recommended, max 512 KB'],
            'og_image' => ['Social sharing image', '1200 × 630 px recommended, JPG/PNG/WebP, max 3 MB'],
        ] as $key => [$label, $hint])
            <div class="upload">
                <div>
                    <label for="f-{{ $key }}"><strong>{{ $label }}</strong></label>
                    <p class="muted small">{{ $hint }}</p>
                    <input id="f-{{ $key }}" type="file" name="{{ $key }}" accept="image/*">
                    @error($key)<p class="error">{{ $message }}</p>@enderror
                    @if ($images[$key])
                        <label class="checkbox"><input type="checkbox" name="remove[]" value="{{ $key }}"> Remove and use the default</label>
                    @endif
                </div>
                <div class="upload__preview">
                    @if ($images[$key])
                        <img src="{{ $images[$key] }}" alt="Current {{ $label }}" width="160" height="160">
                    @else
                        <span class="muted small">No image uploaded (default in use)</span>
                    @endif
                </div>
            </div>
        @endforeach
    </fieldset>

    <fieldset>
        <legend>Default SEO</legend>
        <label class="field"><span>Title template (must include <code>{title}</code>)</span>
            <input name="meta_title_template" value="{{ old('meta_title_template', $values['meta_title_template']) }}" required maxlength="120"></label>
        <label class="field"><span>Meta description (বাংলা)</span>
            <textarea name="meta_description_bn" rows="2" maxlength="300">{{ old('meta_description_bn', $values['meta_description_bn']) }}</textarea></label>
        <label class="field"><span>Meta description (English)</span>
            <textarea name="meta_description_en" rows="2" maxlength="300">{{ old('meta_description_en', $values['meta_description_en']) }}</textarea></label>
    </fieldset>

    <button class="btn btn--primary" type="submit">Save branding</button>
</form>
@endsection
