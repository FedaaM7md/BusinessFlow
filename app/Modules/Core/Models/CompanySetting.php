<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [
        'company_name_ar', 'company_name_en', 'address_ar', 'address_en', 'phone', 'email',
        'tax_number', 'logo_path', 'currency_code', 'currency_symbol_ar', 'currency_symbol_en',
        'default_tax_rate', 'quotation_prefix', 'invoice_prefix', 'payment_prefix',
        'expense_prefix', 'default_locale',
    ];

    protected $casts = [
        'default_tax_rate' => 'integer',
    ];

    public static function current(): self
    {
        return static::query()->first() ?? static::query()->create([
            'company_name_ar' => 'شركتي',
            'company_name_en' => 'My Company',
            'currency_code' => 'EGP',
            'currency_symbol_ar' => 'ج.م',
            'currency_symbol_en' => 'EGP',
            'default_tax_rate' => 1400,
            'default_locale' => 'ar',
        ]);
    }

    public function displayName(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return $locale === 'ar'
            ? ($this->company_name_ar ?: $this->company_name_en)
            : ($this->company_name_en ?: $this->company_name_ar);
    }

    public function displayAddress(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        return $locale === 'ar'
            ? ($this->address_ar ?: $this->address_en)
            : ($this->address_en ?: $this->address_ar);
    }
}
