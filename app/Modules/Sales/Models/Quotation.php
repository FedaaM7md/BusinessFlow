<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Sales\Models;

use App\Models\User;
use App\Modules\CRM\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Quotation extends Model
{
    public const STATUSES = ['draft', 'sent', 'accepted', 'rejected', 'expired'];

    protected $fillable = [
        'number', 'customer_id', 'status', 'issue_date', 'valid_until', 'currency_code',
        'subtotal_minor', 'discount_minor', 'tax_minor', 'total_minor', 'notes', 'terms', 'created_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'valid_until' => 'date',
        'subtotal_minor' => 'integer',
        'discount_minor' => 'integer',
        'tax_minor' => 'integer',
        'total_minor' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(QuotationLine::class)->orderBy('sort_order');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function canConvert(): bool
    {
        return $this->status === 'accepted' && ! $this->invoice()->exists();
    }
}
