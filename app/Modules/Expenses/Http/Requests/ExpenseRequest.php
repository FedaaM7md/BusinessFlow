<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Expenses\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'expense_date' => ['required', 'date'],
            'amount' => ['required', 'regex:/^\d{1,12}(\.\d{1,2})?$/', 'gt:0'],
            'payment_method' => ['required', 'in:cash,bank_transfer,card,cheque,other'],
            'reference' => ['nullable', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:1000'],
        ];
    }
}
