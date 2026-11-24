<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:in,out,adjustment,transfer'],
            'product_id' => ['required', 'exists:products,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'target_warehouse_id' => ['required_if:type,transfer', 'nullable', 'exists:warehouses,id', 'different:warehouse_id'],
            'quantity' => ['required', 'regex:/^-?\d{1,9}(\.\d{1,2})?$/'],
            'unit_cost' => ['nullable', 'regex:/^\d{1,12}(\.\d{1,2})?$/'],
            'moved_at' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'target_warehouse_id.different' => __('messages.transfer_same_warehouse'),
        ];
    }
}
