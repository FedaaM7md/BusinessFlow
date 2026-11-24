<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Core\Services;

use App\Modules\Core\Models\CompanySetting;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    public const TYPES = [
        'quotation' => 'quotation_prefix',
        'invoice' => 'invoice_prefix',
        'payment' => 'payment_prefix',
        'expense' => 'expense_prefix',
    ];

    /**
     * Generate a document number using a row-locked sequence inside a transaction.
     * Format: PREFIX-YEAR-0001.
     */
    public function next(string $type, CarbonInterface|string|null $date = null): string
    {
        if (! isset(self::TYPES[$type])) {
            throw new \InvalidArgumentException("Unknown document type [{$type}].");
        }

        $year = $date ? (int) Carbon::parse($date)->format('Y') : (int) now()->format('Y');

        return DB::transaction(function () use ($type, $year) {
            DB::table('document_sequences')->insertOrIgnore([
                'type' => $type,
                'year' => $year,
                'last_number' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $sequence = DB::table('document_sequences')
                ->where('type', $type)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            $next = ((int) $sequence->last_number) + 1;

            DB::table('document_sequences')
                ->where('id', $sequence->id)
                ->update(['last_number' => $next, 'updated_at' => now()]);

            $settings = CompanySetting::current();
            $prefix = strtoupper((string) $settings->{self::TYPES[$type]} ?: strtoupper(substr($type, 0, 3)));

            return $prefix.'-'.$year.'-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
        });
    }
}
