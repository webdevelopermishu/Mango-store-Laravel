@extends('layouts.admin')
@section('title', 'Products')

@section('content')
<div class="admin-head">
    <h1>Products</h1>
    <a class="btn btn--primary" href="{{ route('admin.products.create') }}">New product</a>
</div>

<form method="get" class="inline-filters">
    <input type="search" name="q" value="{{ request('q') }}" placeholder="Search name or SKU" aria-label="Search products">
    <select name="status" aria-label="Filter by status">
        <option value="">All statuses</option>
        @foreach (['draft' => 'Draft', 'published' => 'Published', 'out_of_stock' => 'Out of stock'] as $value => $label)
            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <button class="btn btn--ghost" type="submit">Filter</button>
</form>

<table class="admin-table">
    <thead><tr><th>Name (বাংলা / English)</th><th>SKU</th><th>Categories</th><th>Variants</th><th>Status</th><th></th></tr></thead>
    <tbody>
    @forelse ($products as $product)
        <tr>
            <td>{{ $product->name_bn }} / {{ $product->name_en }}</td>
            <td>{{ $product->sku ?? '—' }}</td>
            <td>{{ $product->categories->pluck('name_en')->join(', ') ?: '—' }}</td>
            <td>{{ $product->variants->count() }}</td>
            <td><span class="badge @if($product->status !== 'published') badge--muted @endif">{{ str_replace('_', ' ', ucfirst($product->status)) }}</span></td>
            <td class="actions">
                <a href="{{ route('admin.products.edit', $product) }}">Edit</a>
                <form method="post" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Delete this product?')">
                    @csrf @method('DELETE')
                    <button class="link-button" type="submit">Delete</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="6">No products yet.</td></tr>
    @endforelse
    </tbody>
</table>
<div class="pagination">{{ $products->links('vendor.pagination.store') }}</div>
@endsection
