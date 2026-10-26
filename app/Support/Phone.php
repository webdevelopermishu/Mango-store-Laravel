<?php

namespace App\Support;

/** Bangladeshi mobile numbers: accepts Bengali digits, spaces, dashes and a +88 / 88 prefix. */
final class Phone
{
    public const PATTERN = '/^01[3-9]\d{8}$/';

    private const BN_DIGITS = ['০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4',
        '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9'];

    /** Returns the number as 01XXXXXXXXX when possible; other input is returned cleaned so validation can reject it. */
    public static function normalize(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $clean = preg_replace('/[\s\-().]/u', '', strtr($value, self::BN_DIGITS)) ?? '';

        return preg_replace('/^\+?88(?=01)/', '', $clean) ?? $clean;
    }
}
