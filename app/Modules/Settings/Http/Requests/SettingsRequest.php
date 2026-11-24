<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Settings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name_ar' => ['required', 'string', 'max:255'],
            'company_name_en' => ['required', 'string', 'max:255'],
            'address_ar' => ['nullable', 'string', 'max:255'],
            'address_en' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:60'],
            // Demo workspaces get a tighter 1 MB ceiling; SVG is never accepted.
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:'.(demo_enabled() ? 1024 : 2048)],
            'currency_code' => ['required', 'string', 'size:3', 'alpha'],
            'currency_symbol_ar' => ['required', 'string', 'max:10'],
            'currency_symbol_en' => ['required', 'string', 'max:10'],
            'default_tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'quotation_prefix' => ['required', 'alpha_dash', 'max:10'],
            'invoice_prefix' => ['required', 'alpha_dash', 'max:10'],
            'payment_prefix' => ['required', 'alpha_dash', 'max:10'],
            'expense_prefix' => ['required', 'alpha_dash', 'max:10'],
            'default_locale' => ['required', 'in:ar,en'],
        ];
    }
}
