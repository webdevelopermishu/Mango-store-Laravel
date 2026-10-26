<?php

namespace App\Support;

/** Formats integer paisa for display. Bangla uses Bengali digits and lakh grouping (1,00,000). */
final class Money
{
    private const BN_DIGITS = ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪',
        '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'];

    public static function format(int $paisa, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $taka = intdiv($paisa, 100);
        $poisha = $paisa % 100;

        $amount = $locale === 'bn' ? self::lakh($taka) : number_format($taka);
        if ($poisha > 0) {
            $amount .= '.'.str_pad((string) $poisha, 2, '0', STR_PAD_LEFT);
        }

        if ($locale === 'bn') {
            $amount = strtr($amount, self::BN_DIGITS);
        }

        return '৳ '.$amount;
    }

    private static function lakh(int $n): string
    {
        $digits = (string) $n;
        if (strlen($digits) <= 3) {
            return $digits;
        }

        $last3 = substr($digits, -3);
        $rest = substr($digits, 0, -3);
        $rest = strrev(implode(',', str_split(strrev($rest), 2)));

        return $rest.','.$last3;
    }
}
