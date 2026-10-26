<?php

namespace App\Support;

use Illuminate\Http\Request;

/** Builds same-page URLs in another language by swapping the leading locale segment. */
final class LocaleUrl
{
    /** Link target that switches language and returns the visitor to the same page. */
    public static function switch(Request $request, string $locale): string
    {
        return route('lang.switch', ['locale' => $locale, 'to' => $request->getRequestUri()]);
    }

    public static function swap(string $path, string $locale): string
    {
        $pattern = '#^/('.implode('|', config('store.locales')).')(?=/|$)#';
        $rest = preg_replace($pattern, '', $path, 1) ?? '';

        return '/'.$locale.($rest === '' ? '/' : $rest);
    }
}
