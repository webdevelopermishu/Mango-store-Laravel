<?php

namespace App\Http\Controllers;

use App\Support\LocaleUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    /** Switches language and returns to the same page, keeping its query string (filters, search, sort). */
    public function switch(Request $request, string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, config('store.locales'), true), 404);

        $to = (string) $request->query('to', '/');
        // Only same-site relative paths are accepted, so this cannot be used as an open redirect.
        if (! str_starts_with($to, '/') || str_starts_with($to, '//') || str_contains($to, '\\')) {
            $to = '/';
        }

        $parts = parse_url($to) ?: [];
        $target = LocaleUrl::swap($parts['path'] ?? '/', $locale);
        if (isset($parts['query'])) {
            $target .= '?'.$parts['query'];
        }

        return redirect($target)->withCookie(cookie('store_locale', $locale, 60 * 24 * 365));
    }
}
