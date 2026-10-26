<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

/**
 * Reads admin-managed branding. Every value falls back to config/store.php,
 * so an unset field keeps the existing look instead of breaking the page.
 */
final class Branding
{
    public static function title(): string
    {
        return Setting::read('site.title', config('store.name'));
    }

    public static function tagline(): ?string
    {
        return Setting::read('site.tagline_'.app()->getLocale());
    }

    /** Applies the admin's title template, e.g. "{title} | Mango Store". */
    public static function metaTitle(?string $pageTitle = null): string
    {
        $template = Setting::read('site.meta_title_template', '{title} | '.self::title());

        return str_replace('{title}', $pageTitle ?: self::title(), $template);
    }

    public static function metaDescription(?string $pageDescription = null): string
    {
        return $pageDescription
            ?: Setting::read('site.meta_description_'.app()->getLocale(), config('store.meta_description_default'));
    }

    /** Public URL for an uploaded image key (logo, mobile_logo, footer_logo, favicon, og_image), or null. */
    public static function imageUrl(string $key): ?string
    {
        $path = Setting::read("site.{$key}_path");

        return ($path && Storage::disk('public')->exists($path)) ? Storage::disk('public')->url($path) : null;
    }

    /** Header/footer/mobile logo with fallbacks: specific logo, then the main logo, then null (text wordmark). */
    public static function logoFor(string $placement): ?string
    {
        return match ($placement) {
            'mobile' => self::imageUrl('mobile_logo') ?? self::imageUrl('logo'),
            'footer' => self::imageUrl('footer_logo') ?? self::imageUrl('logo'),
            default => self::imageUrl('logo'),
        };
    }
}
