<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;

class HomeController extends Controller
{
    public function index()
    {
        // The hero category is chosen in the admin (Setting "home.hero_category_slug"), so the
        // homepage is not tied to mangoes once the season ends.
        $heroSlug = Setting::read('home.hero_category_slug', 'aam');

        $categories = Category::published()->featured()->get();

        $heroProducts = Product::visible()
            ->whereHas('categories', fn ($q) => $q->where('slug', $heroSlug))
            ->with(['images', 'variants'])
            ->orderByDesc('is_featured_home')
            ->orderBy('sort_order')
            ->limit(4)
            ->get();

        $featuredProducts = Product::visible()
            ->where('is_featured_home', true)
            ->with(['images', 'variants'])
            ->orderBy('sort_order')
            ->limit(8)
            ->get();

        return view('storefront.home', compact('categories', 'heroProducts', 'featuredProducts'));
    }
}
