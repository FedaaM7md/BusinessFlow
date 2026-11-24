<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Core\Services;

use App\Modules\Core\Models\CompanySetting;
use InvalidArgumentException;

final class Money
{
    /**
     * Convert a decimal money string (for example "1,234.50") to minor units.
     * Deliberately string-based so monetary values never pass through a float.
     */
    public static function toMinor(string|int|null $amount): int
    {
        if ($amount === null || $amount === '') {
            return 0;
        }

        $value = trim((string) $amount);
        $value = str_replace([',', ' ', '٫'], '', $value);
        $value = str_replace('−', '-', $value);

        if (! preg_match('/^(-?)(\d+)(?:\.(\d{1,2}))?$/', $value, $matches)) {
            throw new InvalidArgumentException('Invalid monetary amount.');
        }

        $sign = $matches[1] === '-' ? -1 : 1;
        $whole = ltrim($matches[2], '0');
        $whole = $whole === '' ? '0' : $whole;
        $fraction = str_pad($matches[3] ?? '0', 2, '0');

        if (strlen($whole) > 15) {
            throw new InvalidArgumentException('Monetary amount is too large.');
        }

        return $sign * (((int) $whole * 100) + (int) $fraction);
    }

    /** Convert a tax percentage such as "14" to basis points (1400). */
    public static function percentToBasisPoints(string|int|null $percent): int
    {
        $value = trim((string) ($percent ?? '0'));
        $value = str_replace([',', ' '], '', $value);
        if (! preg_match('/^\d{1,3}(?:\.\d{1,2})?$/', $value)) {
            throw new InvalidArgumentException('Invalid tax percentage.');
        }

        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');
        $fraction = str_pad($fraction, 2, '0');

        return ((int) $whole * 100) + (int) $fraction;
    }

    public static function basisPointsToPercent(?int $bp): string
    {
        $bp = (int) ($bp ?? 0);
        $whole = intdiv($bp, 100);
        $fraction = $bp % 100;

        return $fraction === 0
            ? (string) $whole
            : rtrim(number_format($bp / 100, 2, '.', ''), '0');
    }

    public static function format(?int $minor, ?CompanySetting $settings = null, ?string $locale = null): string
    {
        $settings ??= CompanySetting::current();
        $locale ??= app()->getLocale();
        $minor = (int) ($minor ?? 0);

        $negative = $minor < 0;
        $abs = abs($minor);
        $whole = intdiv($abs, 100);
        $fraction = $abs % 100;
        $number = number_format($whole, 0, '.', ',').'.'.str_pad((string) $fraction, 2, '0', STR_PAD_LEFT);

        if ($negative) {
            $number = '-'.$number;
        }

        $symbol = $locale === 'ar' ? $settings->currency_symbol_ar : $settings->currency_symbol_en;

        return $number.' '.$symbol;
    }

    /** Plain decimal input value (no grouping or symbol), e.g. 1234.50. */
    public static function input(?int $minor): string
    {
        $minor = (int) ($minor ?? 0);
        $negative = $minor < 0;
        $abs = abs($minor);

        return ($negative ? '-' : '').intdiv($abs, 100).'.'.str_pad((string) ($abs % 100), 2, '0', STR_PAD_LEFT);
    }

    /** Format a decimal quantity without losing precision. */
    public static function quantity(string|float|int|null $quantity): string
    {
        $formatted = number_format((float) ($quantity ?? 0), 2, '.', ',');

        if (! str_contains($formatted, '.')) {
            return $formatted;
        }

        $value = rtrim(rtrim($formatted, '0'), '.');

        return $value === '' ? '0' : $value;
    }
}
