<?php

namespace App\Models\Concerns;

/**
 * Resolves "{field}_bn" / "{field}_en" columns for the current request locale.
 * Falls back to the other language. localized() then falls back to a configured
 * placeholder; localizedOrNull() returns null so optional fields (SEO, short text)
 * can be omitted instead of showing the placeholder.
 */
trait HasBilingualFields
{
    public function localizedOrNull(string $field): ?string
    {
        $locale = app()->getLocale();
        $other = $locale === 'bn' ? 'en' : 'bn';

        $primary = $this->getAttribute("{$field}_{$locale}");
        if (filled($primary)) {
            return $primary;
        }

        $fallback = $this->getAttribute("{$field}_{$other}");

        return filled($fallback) ? $fallback : null;
    }

    public function localized(string $field): string
    {
        return $this->localizedOrNull($field) ?? (string) config('store.untranslated_placeholder');
    }
}
