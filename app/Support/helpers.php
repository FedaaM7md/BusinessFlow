<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

use App\Modules\Core\Models\CompanySetting;
use App\Modules\Core\Services\Money;

if (! function_exists('company')) {
    function company(): CompanySetting
    {
        return CompanySetting::current();
    }
}

if (! function_exists('money')) {
    function money(int|string|null $minor): string
    {
        return Money::format($minor === null ? 0 : (int) $minor);
    }
}

if (! function_exists('money_input')) {
    function money_input(int|string|null $minor): string
    {
        return Money::input($minor === null ? 0 : (int) $minor);
    }
}

if (! function_exists('quantity')) {
    function quantity(string|int|float|null $value): string
    {
        return Money::quantity($value);
    }
}

if (! function_exists('current_locale')) {
    function current_locale(): string
    {
        return app()->getLocale() === 'ar' ? 'ar' : 'en';
    }
}

if (! function_exists('is_rtl')) {
    function is_rtl(): bool
    {
        return current_locale() === 'ar';
    }
}

if (! function_exists('demo_enabled')) {
    function demo_enabled(): bool
    {
        return (bool) config('demo.enabled', true);
    }
}

if (! function_exists('status_classes')) {
    function status_classes(string $status): string
    {
        return match ($status) {
            'paid', 'accepted', 'active', 'in' => 'bg-positive-50 text-positive-700 ring-positive-600/20',
            'issued', 'sent', 'partially_paid' => 'bg-brand-50 text-brand-700 ring-brand-500/20',
            'draft', 'adjustment' => 'bg-brand-50 text-brand-500 ring-brand-500/10',
            'cancelled', 'rejected', 'expired', 'out' => 'bg-negative-50 text-negative-700 ring-negative-600/20',
            'transfer' => 'bg-accent-50 text-accent-700 ring-accent-500/20',
            default => 'bg-accent-50 text-accent-700 ring-accent-500/20',
        };
    }
}
