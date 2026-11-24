<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Catalog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_tracked' => $this->input('type') === 'service' ? false : $this->boolean('is_tracked'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'sku' => ['required', 'string', 'max:60', Rule::unique('products', 'sku')->ignore($product?->id)],
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'in:product,service'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'unit' => ['required', 'string', 'max:30'],
            'price' => ['required', 'regex:/^\d{1,12}(\.\d{1,2})?$/'],
            'cost' => ['nullable', 'regex:/^\d{1,12}(\.\d{1,2})?$/'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'is_tracked' => ['boolean'],
            'low_stock_threshold' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
