<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ProductController extends Controller
{
    public function show(Product $product)
    {
        abort_unless(in_array($product->status, [Product::STATUS_PUBLISHED, Product::STATUS_OUT_OF_STOCK], true), 404);

        $product->load(['images', 'variants', 'categories']);

        $related = Product::visible()
            ->whereKeyNot($product->id)
            ->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $product->categories->pluck('id')))
            ->with(['images', 'variants'])
            ->limit(4)
            ->get();

        return view('storefront.product', compact('product', 'related'));
    }
}
