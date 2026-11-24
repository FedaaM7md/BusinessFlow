<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\CRM\Models;

use App\Modules\Sales\Models\Invoice;
use App\Modules\Sales\Models\Payment;
use App\Modules\Sales\Models\Quotation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'type', 'name', 'company_name', 'email', 'phone', 'address', 'city',
        'country', 'tax_number', 'notes', 'is_active', 'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('company_name', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%");
        });
    }

    public function totalInvoicedMinor(): int
    {
        return (int) $this->invoices()->whereNotIn('status', ['draft', 'cancelled'])->sum('total_minor');
    }

    public function totalPaidMinor(): int
    {
        return (int) $this->payments()->sum('amount_minor');
    }

    public function balanceMinor(): int
    {
        return $this->totalInvoicedMinor() - $this->totalPaidMinor();
    }
}
