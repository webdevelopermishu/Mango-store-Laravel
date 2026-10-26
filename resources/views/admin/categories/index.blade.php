@extends('layouts.admin')
@section('title', 'Categories')

@section('content')
<div class="admin-head">
    <h1>Categories</h1>
    <a class="btn btn--primary" href="{{ route('admin.categories.create') }}">New category</a>
</div>

<form method="post" action="{{ route('admin.categories.reorder') }}">
    @csrf
    <table class="admin-table">
        <thead><tr><th>Order</th><th>Name (বাংলা / English)</th><th>Slug</th><th>Parent</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse ($categories as $category)
            <tr>
                <td><input type="number" min="0" name="order[{{ $category->id }}]" value="{{ $category->sort_order }}" class="narrow" aria-label="Sort order for {{ $category->name_en }}"></td>
                <td>{{ $category->name_bn }} / {{ $category->name_en }}</td>
                <td><code>{{ $category->slug }}</code></td>
                <td>{{ $category->parent?->name_en ?? '—' }}</td>
                <td>
                    @if ($category->is_published)<span class="badge">Published</span>@else<span class="badge badge--muted">Hidden</span>@endif
                    @if ($category->is_featured)<span class="badge">Featured</span>@endif
                </td>
                <td class="actions">
                    <a href="{{ route('admin.categories.edit', $category) }}">Edit</a>
                    <form method="post" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Delete this category? Its products will be kept.')">
                        @csrf @method('DELETE')
                        <button class="link-button" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6">No categories yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    @if ($categories->isNotEmpty())
        <button class="btn btn--ghost" type="submit">Save order</button>
    @endif
</form>
@endsection
