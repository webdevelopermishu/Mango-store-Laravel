<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Models\Category;
use App\Models\Product;
use App\Support\Uploads;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('parent')->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.form', [
            'category' => new Category(['is_published' => true, 'sort_order' => 0]),
            'parents' => $this->parentOptions(),
            'products' => Product::orderBy('name_en')->get(),
            'selectedProducts' => [],
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $category = Category::create($this->attributes($request));
        $this->syncProducts($category, $request);

        return redirect()->route('admin.categories.index')->with('status', 'Category created.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.form', [
            'category' => $category,
            'parents' => $this->parentOptions($category),
            'products' => Product::orderBy('name_en')->get(),
            'selectedProducts' => $category->products()->pluck('products.id')->all(),
        ]);
    }

    public function update(StoreCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($this->attributes($request, $category));
        $this->syncProducts($category, $request);

        return redirect()->route('admin.categories.index')->with('status', 'Category updated.');
    }

    /** Categories are removed without deleting their products; the pivot rows are cleaned up by the database. */
    public function destroy(Category $category): RedirectResponse
    {
        Uploads::remove($category->image_path, 'categories');
        Uploads::remove($category->banner_path, 'categories');
        $category->delete();

        return redirect()->route('admin.categories.index')->with('status', 'Category deleted. Its products were kept.');
    }

    /** Saves the sort order from the index table: order[category_id] = position. */
    public function reorder(Request $request): RedirectResponse
    {
        $request->validate(['order' => ['required', 'array'], 'order.*' => ['integer', 'min:0']]);

        DB::transaction(function () use ($request) {
            foreach ($request->input('order') as $id => $position) {
                Category::whereKey((int) $id)->update(['sort_order' => (int) $position]);
            }
        });

        return redirect()->route('admin.categories.index')->with('status', 'Order saved.');
    }

    private function attributes(StoreCategoryRequest $request, ?Category $current = null): array
    {
        $data = $request->safe()->except(['image', 'banner', 'product_ids']);
        $data['is_published'] = $request->boolean('is_published');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['sort_order'] = (int) $request->input('sort_order', 0);
        $data['parent_id'] = $request->integer('parent_id') ?: null;

        if ($request->hasFile('image')) {
            $data['image_path'] = Uploads::replace($current?->image_path, $request->file('image'), 'categories');
        }
        if ($request->hasFile('banner')) {
            $data['banner_path'] = Uploads::replace($current?->banner_path, $request->file('banner'), 'categories');
        }

        return $data;
    }

    private function syncProducts(Category $category, StoreCategoryRequest $request): void
    {
        $category->products()->sync($request->input('product_ids', []));
    }

    private function parentOptions(?Category $exclude = null)
    {
        return Category::whereNull('parent_id')
            ->when($exclude, fn ($q) => $q->whereKeyNot($exclude->id))
            ->orderBy('sort_order')
            ->get();
    }
}
