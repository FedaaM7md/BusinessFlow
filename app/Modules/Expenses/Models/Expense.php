<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Expenses\Models;

use App\Models\User;
use App\Modules\Suppliers\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    public const METHODS = ['cash', 'bank_transfer', 'card', 'cheque', 'other'];

    protected $fillable = [
        'number', 'expense_category_id', 'supplier_id', 'expense_date', 'amount_minor',
        'payment_method', 'reference', 'description', 'created_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount_minor' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('number', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhere('reference', 'like', "%{$term}%");
        });
    }
}
