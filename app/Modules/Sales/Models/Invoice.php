<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Sales\Models;

use App\Models\User;
use App\Modules\CRM\Models\Customer;
use App\Modules\Inventory\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    public const STATUSES = ['draft', 'issued', 'partially_paid', 'paid', 'cancelled'];

    protected $fillable = [
        'number', 'customer_id', 'quotation_id', 'warehouse_id', 'status', 'issue_date', 'due_date',
        'currency_code', 'subtotal_minor', 'discount_minor', 'tax_minor', 'total_minor', 'notes',
        'cancelled_at', 'cancelled_by', 'cancellation_reason', 'created_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'subtotal_minor' => 'integer',
        'discount_minor' => 'integer',
        'tax_minor' => 'integer',
        'total_minor' => 'integer',
        'cancelled_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class)->orderBy('sort_order');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function paidMinor(): int
    {
        return (int) $this->payments()->sum('amount_minor');
    }

    public function balanceMinor(): int
    {
        return max(0, $this->total_minor - $this->paidMinor());
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function canIssue(): bool
    {
        return $this->status === 'draft';
    }

    public function canCancel(): bool
    {
        return in_array($this->status, ['issued', 'partially_paid'], true) && $this->paidMinor() === 0;
    }

    public function canAcceptPayments(): bool
    {
        return in_array($this->status, ['issued', 'partially_paid'], true) && $this->balanceMinor() > 0;
    }

    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->isBefore(today())
            && in_array($this->status, ['issued', 'partially_paid'], true);
    }

    public function scopeOutstanding(Builder $query): Builder
    {
        return $query->whereIn('status', ['issued', 'partially_paid']);
    }
}
