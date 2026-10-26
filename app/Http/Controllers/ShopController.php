<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        return $this->render($request, null);
    }

    public function category(Request $request, Category $category)
    {
        abort_unless($category->is_published, 404);

        return $this->render($request, $category);
    }

    private function render(Request $request, ?Category $category)
    {
        $effectivePrice = 'COALESCE(product_variants.sale_price_paisa, product_variants.regular_price_paisa)';

        $query = Product::visible()
            ->with(['images', 'variants'])
            ->when($category, fn (Builder $q) => $q->whereHas('categories', fn ($c) => $c->whereKey($category->id)))
            ->when($request->filled('q'), fn (Builder $q) => $q->search($request->string('q')->toString()))
            ->when($request->boolean('in_stock'), fn (Builder $q) => $q->inStock())
            ->when($request->filled('min_price'), fn (Builder $q) => $q->whereHas('variants', fn ($v) => $v->whereRaw("$effectivePrice >= ?", [(int) round((float) $request->input('min_price') * 100)])))
            ->when($request->filled('max_price'), fn (Builder $q) => $q->whereHas('variants', fn ($v) => $v->whereRaw("$effectivePrice <= ?", [(int) round((float) $request->input('max_price') * 100)])));

        match ($request->string('sort')->toString()) {
            'price_asc' => $query->orderByRaw('(SELECT MIN('.$effectivePrice.') FROM product_variants WHERE product_variants.product_id = products.id) ASC'),
            'price_desc' => $query->orderByRaw('(SELECT MIN('.$effectivePrice.') FROM product_variants WHERE product_variants.product_id = products.id) DESC'),
            'name' => $query->orderBy('name_'.app()->getLocale()),
            default => $query->orderBy('sort_order')->orderByDesc('id'),
        };

        $products = $query->paginate(12)->withQueryString();

        $navCategories = Category::published()->whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->published()])
            ->orderBy('sort_order')
            ->get();

        return view('storefront.shop', [
            'products' => $products,
            'category' => $category,
            'navCategories' => $navCategories,
        ]);
    }
}
