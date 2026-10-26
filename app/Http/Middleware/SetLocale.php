<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Chooses the storefront language from the URL segment, then the saved cookie,
 * then the default (Bangla). Also makes route('...') default to the active locale.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locales = config('store.locales');
        $route = $request->route();
        $fromUrl = $route?->parameter('locale');
        $locale = in_array($fromUrl, $locales, true)
            ? $fromUrl
            : $request->cookie('store_locale');

        if (! in_array($locale, $locales, true)) {
            $locale = config('store.default_locale');
        }

        app()->setLocale($locale);
        URL::defaults(['locale' => $locale]);

        // Laravel passes route parameters to controller methods by position. Removing
        // {locale} here means show(Product $product) receives the product, not "bn".
        $route?->forgetParameter('locale');

        if ($request->cookie('store_locale') !== $locale) {
            cookie()->queue(cookie('store_locale', $locale, 60 * 24 * 365));
        }

        return $next($request);
    }
}
