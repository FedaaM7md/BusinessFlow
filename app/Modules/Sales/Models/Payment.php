<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Sales\Models;

use App\Models\User;
use App\Modules\CRM\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const METHODS = ['cash', 'bank_transfer', 'card', 'cheque', 'other'];

    protected $fillable = [
        'number', 'invoice_id', 'customer_id', 'amount_minor', 'method',
        'payment_date', 'reference', 'notes', 'created_by',
    ];

    protected $casts = [
        'amount_minor' => 'integer',
        'payment_date' => 'date',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
