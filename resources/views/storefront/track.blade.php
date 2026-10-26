@extends('layouts.storefront')

@section('meta_title', \App\Support\Branding::metaTitle(__('storefront.track.title')))
@push('head')<meta name="robots" content="noindex">@endpush

@section('content')
<div class="container section narrow-page">
    <h1 class="page-title">{{ __('storefront.track.title') }}</h1>
    <p class="lead">{{ __('storefront.track.intro') }}</p>

    @if ($notFound)
        <p class="flash flash--error" role="alert">{{ __('storefront.track.not_found') }}</p>
    @endif

    <form method="get" action="{{ route('order.track') }}" class="panel track-form">
        <label class="field">
            <span>{{ __('storefront.track.order_number') }}</span>
            <input name="order_number" value="{{ old('order_number', request('order_number')) }}" required maxlength="20"
                   autocapitalize="characters" spellcheck="false" placeholder="{{ __('storefront.track.order_hint') }}">
            @error('order_number')<span class="error">{{ $message }}</span>@enderror
        </label>
        <label class="field">
            <span>{{ __('storefront.track.phone') }}</span>
            <input name="phone" value="{{ old('phone', request('phone')) }}" required inputmode="tel" autocomplete="tel"
                   placeholder="{{ __('storefront.checkout.phone_hint') }}">
            @error('phone')<span class="error">{{ $message }}</span>@enderror
        </label>
        <button class="btn btn--primary" type="submit">{{ __('storefront.track.submit') }}</button>
    </form>
</div>
@endsection
