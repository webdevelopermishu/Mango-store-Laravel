<<<<<<< HEAD
# Mango Store — Laravel overlay (bilingual Bangla/English store)

This folder contains the application code for the store as a **Laravel 11 overlay**: it
is meant to be copied into a fresh `laravel/laravel` project. It was not run end-to-end in
this session, because the build sandbox could not reach Packagist (see "Verification").

## What is included

| Area | Files |
|---|---|
| Branding (title, logos, favicon, tagline, SEO defaults, OG image) | `app/Http/Controllers/Admin/BrandingController.php`, `app/Http/Requests/Admin/StoreBrandingRequest.php`, `app/Support/Branding.php`, `app/Models/Setting.php`, `resources/views/admin/branding.blade.php` |
| Categories (bilingual, nested one level, featured, reorder, enable/disable) | `app/Models/Category.php`, `app/Http/Controllers/Admin/CategoryController.php`, `resources/views/admin/categories/*` |
| Products (bilingual, multi-image, SKU, variants with BDT prices, stock, seasonality, draft/published/out-of-stock) | `app/Models/Product*.php`, `app/Http/Controllers/Admin/ProductController.php`, `resources/views/admin/products/*` |
| Money as integer paisa, Bengali digits and lakh grouping | `app/Support/Money.php` |
| Bilingual storefront at `/bn/...` and `/en/...`, default Bangla, cookie preference, same-page switching | `app/Http/Middleware/SetLocale.php`, `app/Http/Controllers/LanguageController.php`, `app/Support/LocaleUrl.php`, `routes/web.php` |
| Translation system (centralised strings + field-level fallback) | `lang/bn/storefront.php`, `lang/en/storefront.php`, `app/Models/Concerns/HasBilingualFields.php` |
| Shop with category navigation, search, price filter, sort, in-stock filter | `app/Http/Controllers/ShopController.php`, `resources/views/storefront/shop.blade.php` |
| Add to Cart: real session cart, server-side price and stock checks, double-click and retry protection, drawer, reduced motion | `app/Services/Cart.php`, `app/Http/Controllers/CartController.php`, `resources/views/components/add-to-cart.blade.php`, `public/js/storefront.js`, `public/css/storefront.css` |
| Typography: self-hosted WOFF2 — Hind Siliguri (UI, Bangla), Noto Serif Bengali (Bangla headings), DM Sans (Latin UI), DM Serif Display (English headings) | `public/fonts/` |
| SEO: per-language titles and descriptions, canonical, `hreflang` bn/en/x-default, Product JSON-LD in the page's language | `resources/views/layouts/storefront.blade.php`, `resources/views/storefront/product.blade.php` |
| Checkout: contact and delivery details, inside/outside Dhaka delivery fee, cash on delivery, bKash and Nagad (manual Send Money + transaction ID), Bangladeshi phone validation (accepts Bengali digits and +88) | `app/Http/Controllers/CheckoutController.php`, `app/Http/Requests/CheckoutRequest.php`, `app/Services/PlaceOrder.php`, `app/Support/Phone.php`, `resources/views/storefront/checkout.blade.php` |
| Orders: locked stock check and reduction, price snapshot per item, duplicate-submission guard, price-change check, private order link | `app/Models/Order.php`, `app/Models/OrderItem.php`, `database/migrations/*_orders_*`, `*_order_items_*` |
| Order confirmation and tracking (order number + phone), status timeline, payment status | `app/Http/Controllers/OrderController.php`, `resources/views/storefront/order.blade.php`, `resources/views/storefront/track.blade.php` |
| Cart page: change quantity, remove, low-stock warning, proceed to checkout | `resources/views/storefront/cart.blade.php`, `app/Services/Cart.php` |
| Admin orders (search, filter, status and payment updates, cancel returns stock) and checkout settings (fees, methods, bKash/Nagad numbers, instructions) | `app/Http/Controllers/Admin/OrderController.php`, `app/Http/Controllers/Admin/CheckoutSettingsController.php`, `resources/views/admin/orders/*`, `resources/views/admin/checkout-settings.blade.php` |
| Starter data (sample products, all drafts, placeholder prices) | `database/seeders/StoreSeeder.php` |

## Install

Run these on your machine (PHP 8.2+, Composer, Node optional):

```bash
composer create-project laravel/laravel mango-store
cd mango-store

# 1. Admin login (optional but needed for /admin). Install BEFORE copying this overlay,
#    because Breeze edits routes/web.php.
composer require laravel/breeze --dev
php artisan breeze:install blade

# 2. Copy this overlay into the project (overwrites routes/web.php and config defaults only).
#    Folders: app, config, database, lang, public, resources/views, routes.

# 3. Keep Breeze's auth routes: open routes/web.php and add at the bottom:
#    require __DIR__.'/auth.php';

# 4. Register the admin middleware alias in bootstrap/app.php, inside ->withMiddleware():
#    $middleware->alias(['admin' => \App\Http\Middleware\EnsureAdmin::class]);

# 5. Environment (.env)
#    APP_LOCALE=bn
#    APP_FALLBACK_LOCALE=en
#    STORE_NAME="Mango Store"

php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Then register an account at `/register`, make it an admin with
`php artisan tinker` → `\App\Models\User::where('email','you@example.com')->update(['is_admin'=>true]);`,
and open `/admin/branding`.

Before taking real orders, open `/admin/checkout` and set your delivery charges (the defaults
of ৳60 / ৳120 are placeholders), choose payment methods, and enter your bKash/Nagad
receiving numbers. bKash and Nagad only appear at checkout once their number is saved.

Visit `/bn` (Bangla) or `/en` (English). The seeded products are drafts; publish them from
`/admin/products` after replacing the sample names, prices and images.

Homepage hero category defaults to the slug `aam`. Change it with
`php artisan tinker` → `\App\Models\Setting::write('home.hero_category_slug', 'gur');`.

## Verification status

- All PHP files lint clean under PHP 8.3 (`php -l`).
- Blade block directives (`@if`/`@endif`, `@foreach`, `@section`, `@push`, …) balance in every view.
- The add-to-cart JavaScript parses.
- Phone normalisation was run against sample inputs (Bengali digits, `+880`, invalid prefixes).
- Bangla and English language files have identical keys, and every translation key used in code exists.
- **Not run:** `composer install`, the Laravel bootstrap, migrations, the seeder, HTTP
  requests, and browser testing. Expect a short round of fixes on first run. Please report
  any error message and it can be fixed quickly.

## How payment works

There is no payment-gateway integration. Cash on delivery orders arrive as "Not paid yet".
bKash and Nagad use manual "Send Money": the customer sends the total to your number and
enters the number they paid from and the transaction ID. Those orders arrive as "Being
verified"; check the transaction in your bKash/Nagad account, then set the payment to Paid
in `/admin/orders`. A merchant gateway (bKash PGW, SSLCommerz, etc.) can be added later; it
needs a merchant account and API credentials.

## Not built yet (from the brief)

- Customer notifications (SMS or email when an order is placed or its status changes).
- Customer accounts and order history. Customers check out as guests and use the order link or tracking page.
- FAQs, policy pages and blog.
- Admin screens for "Content → Translation Management" and "Appearance → Homepage Sections".
  Homepage placement today uses the `is_featured` (categories) and `is_featured_home`
  (products) flags, plus the hero-category setting above.
- A dedicated "SEO → Language-Specific Metadata" screen. The fields exist on each category
  and product form instead.
- Multilingual XML sitemap, and per-language URL slugs (one English-safe slug per item).
- Favicon size generation. The admin uploads one favicon (PNG, ICO or WebP). SVG is
  refused on purpose because uploaded SVG can contain script.
- Image resizing and WebP conversion (uploads are stored as-is).
- The admin UI is in English. The storefront is fully bilingual.
- Automated tests.
=======
# Mango-Store
This web is build to sell and sourcing product in Bangladeshi market
>>>>>>> cd51f9b5e3d3c53e05b66cd9938e36ade530611c
