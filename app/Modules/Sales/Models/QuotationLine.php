<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Sales\Models;

use App\Modules\Catalog\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationLine extends Model
{
    protected $fillable = [
        'quotation_id', 'product_id', 'description', 'quantity', 'unit_price_minor',
        'tax_rate', 'subtotal_minor', 'discount_minor', 'tax_minor', 'total_minor', 'sort_order',
    ];

    protected $casts = [
        'quantity' => 'float',
        'unit_price_minor' => 'integer',
        'tax_rate' => 'integer',
        'subtotal_minor' => 'integer',
        'discount_minor' => 'integer',
        'tax_minor' => 'integer',
        'total_minor' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }
}
