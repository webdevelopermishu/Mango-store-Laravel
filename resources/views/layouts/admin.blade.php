<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title', 'Admin') · Store admin</title>
    <link rel="stylesheet" href="{{ asset('css/storefront.css') }}">
</head>
<body class="admin">
<header class="admin-bar">
    <strong>Store admin</strong>
    <nav>
        <a href="{{ route('admin.orders.index') }}">Orders</a>
        <a href="{{ route('admin.products.index') }}">Products</a>
        <a href="{{ route('admin.categories.index') }}">Categories</a>
        <a href="{{ route('admin.branding.edit') }}">Branding</a>
        <a href="{{ route('admin.checkout.edit') }}">Checkout settings</a>
        <a href="{{ url('/'.config('store.default_locale')) }}" target="_blank" rel="noopener">View store</a>
    </nav>
</header>
<main class="container admin-main">
    @if (session('status'))<div class="flash flash--ok" role="status">{{ session('status') }}</div>@endif
    @if ($errors->any())
        <div class="flash flash--error" role="alert">
            <strong>Please fix the following:</strong>
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    @yield('content')
</main>
</body>
</html>
