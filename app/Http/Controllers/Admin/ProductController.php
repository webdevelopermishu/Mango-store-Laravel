<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Support\Uploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['categories', 'variants'])
            ->when($request->filled('q'), fn ($q) => $q->search($request->string('q')->toString()))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        return view('admin.products.form', [
            'product' => new Product(['status' => Product::STATUS_DRAFT, 'unit' => 'piece', 'sort_order' => 0]),
            'categories' => Category::orderBy('sort_order')->get(),
            'selectedCategories' => [],
            'variantRows' => $this->blankRows(),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = DB::transaction(function () use ($request) {
            $product = Product::create($this->attributes($request));
            $this->sync($product, $request);

            return $product;
        });

        return redirect()->route('admin.products.edit', $product)->with('status', 'Product created.');
    }

    public function edit(Product $product)
    {
        $product->load(['images', 'variants', 'categories']);

        $rows = $product->variants->values();
        while ($rows->count() < $product->variants->count() + 2) {
            $rows->push(new ProductVariant());
        }

        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::orderBy('sort_order')->get(),
            'selectedCategories' => $product->categories->pluck('id')->all(),
            'variantRows' => $rows,
        ]);
    }

    public function update(StoreProductRequest $request, Product $product): RedirectResponse
    {
        DB::transaction(function () use ($request, $product) {
            $product->update($this->attributes($request));
            $this->sync($product, $request);
        });

        return redirect()->route('admin.products.edit', $product)->with('status', 'Product saved.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->images->each(fn (ProductImage $image) => Uploads::remove($image->path, 'products'));
        $product->delete();

        return redirect()->route('admin.products.index')->with('status', 'Product deleted.');
    }

    private function attributes(StoreProductRequest $request): array
    {
        return [
            'slug' => $request->string('slug')->toString(),
            'sku' => $request->input('sku') ?: null,
            'name_bn' => $request->string('name_bn')->toString(),
            'name_en' => $request->string('name_en')->toString(),
            'short_description_bn' => $request->input('short_description_bn'),
            'short_description_en' => $request->input('short_description_en'),
            'description_bn' => $request->input('description_bn'),
            'description_en' => $request->input('description_en'),
            'status' => $request->string('status')->toString(),
            'unit' => $request->string('unit')->toString(),
            'is_seasonal' => $request->boolean('is_seasonal'),
            'season_start_month' => $request->boolean('is_seasonal') ? $request->integer('season_start_month') : null,
            'season_end_month' => $request->boolean('is_seasonal') ? $request->integer('season_end_month') : null,
            'is_featured_home' => $request->boolean('is_featured_home'),
            'sort_order' => (int) $request->input('sort_order', 0),
            'seo_title_bn' => $request->input('seo_title_bn'),
            'seo_title_en' => $request->input('seo_title_en'),
            'seo_description_bn' => $request->input('seo_description_bn'),
            'seo_description_en' => $request->input('seo_description_en'),
        ];
    }

    /** Categories, images and variants. Variants not present in the submission are removed. */
    private function sync(Product $product, StoreProductRequest $request): void
    {
        $product->categories()->sync($request->input('category_ids', []));

        $removeIds = array_map('intval', (array) $request->input('remove_image_ids', []));
        if ($removeIds) {
            $product->images()->whereIn('id', $removeIds)->get()->each(function (ProductImage $image) {
                Uploads::remove($image->path, 'products');
                $image->delete();
            });
        }

        $nextOrder = (int) $product->images()->max('sort_order') + 1;
        foreach ($request->file('images', []) as $file) {
            $product->images()->create([
                'path' => $file->store('products', 'public'),
                'sort_order' => $nextOrder++,
            ]);
        }

        $keep = [];
        foreach ((array) $request->input('variants', []) as $position => $row) {
            $row = is_array($row) ? $row : [];
            if (blank($row['label_bn'] ?? null) && blank($row['label_en'] ?? null) && blank($row['regular_price'] ?? null)) {
                continue;
            }

            $attributes = [
                'sku' => $row['sku'] ?: null,
                'label_bn' => $row['label_bn'],
                'label_en' => $row['label_en'],
                'weight_grams' => filled($row['weight_grams'] ?? null) ? (int) $row['weight_grams'] : null,
                // Prices are stored as integer paisa. Rounding avoids float drift like 59.99 * 100 = 5998.99...
                'regular_price_paisa' => (int) round((float) $row['regular_price'] * 100),
                'sale_price_paisa' => filled($row['sale_price'] ?? null) ? (int) round((float) $row['sale_price'] * 100) : null,
                'stock_quantity' => filled($row['stock_quantity'] ?? null) ? (int) $row['stock_quantity'] : null,
                'is_active' => (bool) ($row['is_active'] ?? false),
                'sort_order' => $position,
            ];

            $existing = filled($row['id'] ?? null) ? $product->variants()->whereKey((int) $row['id'])->first() : null;
            if ($existing) {
                $existing->update($attributes);
                $keep[] = $existing->id;
            } else {
                $keep[] = $product->variants()->create($attributes)->id;
            }
        }

        $product->variants()->whereNotIn('id', $keep)->delete();
    }

    private function blankRows()
    {
        return collect([new ProductVariant(), new ProductVariant()]);
    }
}
