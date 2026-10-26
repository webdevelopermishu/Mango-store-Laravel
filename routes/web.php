<?php

use App\Http\Controllers\Admin\BrandingController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CheckoutSettingsController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShopController;
use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Root: send the visitor to their saved language (default Bangla).
Route::get('/', function (Request $request) {
    $locale = $request->cookie('store_locale');
    if (! in_array($locale, config('store.locales'), true)) {
        $locale = config('store.default_locale');
    }

    return redirect('/'.$locale);
})->name('root');

Route::get('/lang/{locale}', [LanguageController::class, 'switch'])
    ->where('locale', 'bn|en')
    ->name('lang.switch');

// Storefront: every page lives under /bn/... or /en/...
Route::prefix('{locale}')
    ->where(['locale' => 'bn|en'])
    ->middleware(SetLocale::class)
    ->group(function () {
        Route::get('/', [HomeController::class, 'index'])->name('home');
        Route::get('/shop', [ShopController::class, 'index'])->name('shop');
        Route::get('/shop/{category}', [ShopController::class, 'category'])->name('shop.category');
        Route::get('/product/{product}', [ProductController::class, 'show'])->name('product.show');

        Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
        Route::post('/cart/items', [CartController::class, 'store'])->name('cart.add');
        Route::patch('/cart/items/{variant}', [CartController::class, 'update'])->name('cart.update');
        Route::delete('/cart/items/{variant}', [CartController::class, 'destroy'])->name('cart.remove');

        Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
        Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:10,1')->name('checkout.store');

        Route::get('/order/{order:order_number}', [OrderController::class, 'show'])->name('order.show');
        Route::get('/track', [OrderController::class, 'track'])->middleware('throttle:20,1')->name('order.track');
    });

// Admin: requires login (see README for installing Breeze) and the is_admin flag.
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        Route::get('branding', [BrandingController::class, 'edit'])->name('branding.edit');
        Route::put('branding', [BrandingController::class, 'update'])->name('branding.update');

        Route::get('checkout', [CheckoutSettingsController::class, 'edit'])->name('checkout.edit');
        Route::put('checkout', [CheckoutSettingsController::class, 'update'])->name('checkout.update');

        Route::post('categories/reorder', [AdminCategoryController::class, 'reorder'])->name('categories.reorder');
        Route::resource('categories', AdminCategoryController::class)->except('show');
        Route::resource('products', AdminProductController::class)->except('show');

        Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::put('orders/{order}', [AdminOrderController::class, 'update'])->name('orders.update');
    });




// Without this, the Breeze login and registration routes may not work.
require __DIR__.'/auth.php';
