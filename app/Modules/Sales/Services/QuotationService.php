<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Sales\Services;

use App\Models\User;
use App\Modules\Core\Services\ActivityLogger;
use App\Modules\Core\Services\DocumentNumberService;
use App\Modules\Core\Services\Money;
use App\Modules\Sales\Models\Invoice;
use App\Modules\Sales\Models\Quotation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuotationService
{
    public function __construct(
        private readonly ActivityLogger $activity,
        private readonly DocumentNumberService $numbers,
        private readonly DocumentCalculator $calculator,
        private readonly InvoiceService $invoices,
    ) {}

    public function create(array $data, User $actor): Quotation
    {
        return DB::transaction(function () use ($data, $actor) {
            $calculated = $this->calculate($data);
            $quotation = Quotation::create([
                'number' => $this->numbers->next('quotation', $data['issue_date']),
                'customer_id' => $data['customer_id'],
                'status' => 'draft',
                'issue_date' => $data['issue_date'],
                'valid_until' => $data['valid_until'] ?? null,
                'currency_code' => company()->currency_code,
                'subtotal_minor' => $calculated['subtotal_minor'],
                'discount_minor' => $calculated['discount_minor'],
                'tax_minor' => $calculated['tax_minor'],
                'total_minor' => $calculated['total_minor'],
                'notes' => $data['notes'] ?? null,
                'terms' => $data['terms'] ?? null,
                'created_by' => $actor->id,
            ]);
            $quotation->lines()->createMany($calculated['lines']);

            $this->activity->log('sales', 'quotation_created', 'Quotation '.$quotation->number.' created', $quotation, [
                'total_minor' => ['from' => null, 'to' => $quotation->total_minor],
            ]);

            return $quotation->load('lines');
        });
    }

    public function update(Quotation $quotation, array $data): Quotation
    {
        return DB::transaction(function () use ($quotation, $data) {
            if (! $quotation->isDraft()) {
                throw ValidationException::withMessages(['status' => __('messages.only_draft_editable')]);
            }

            $calculated = $this->calculate($data);
            $quotation->update([
                'customer_id' => $data['customer_id'],
                'issue_date' => $data['issue_date'],
                'valid_until' => $data['valid_until'] ?? null,
                'subtotal_minor' => $calculated['subtotal_minor'],
                'discount_minor' => $calculated['discount_minor'],
                'tax_minor' => $calculated['tax_minor'],
                'total_minor' => $calculated['total_minor'],
                'notes' => $data['notes'] ?? null,
                'terms' => $data['terms'] ?? null,
            ]);

            $quotation->lines()->delete();
            $quotation->lines()->createMany($calculated['lines']);

            $this->activity->log('sales', 'quotation_updated', 'Quotation '.$quotation->number.' updated', $quotation);

            return $quotation->refresh()->load('lines');
        });
    }

    public function updateStatus(Quotation $quotation, string $status): Quotation
    {
        $allowed = match ($quotation->status) {
            'draft' => ['sent'],
            'sent' => ['accepted', 'rejected', 'expired'],
            default => [],
        };

        if (! in_array($status, $allowed, true)) {
            throw ValidationException::withMessages(['status' => __('messages.invalid_status_transition')]);
        }

        return DB::transaction(function () use ($quotation, $status) {
            $from = $quotation->status;
            $quotation->update(['status' => $status]);
            $this->activity->log('sales', 'quotation_status_changed', 'Quotation '.$quotation->number.' changed to '.$status, $quotation, [
                'status' => ['from' => $from, 'to' => $status],
            ]);

            return $quotation->refresh();
        });
    }

    public function convertToInvoice(Quotation $quotation, User $actor): Invoice
    {
        return DB::transaction(function () use ($quotation, $actor) {
            if ($quotation->status !== 'accepted') {
                throw ValidationException::withMessages(['status' => __('messages.quotation_must_be_accepted')]);
            }

            if ($quotation->invoice()->exists()) {
                throw ValidationException::withMessages(['status' => __('messages.quotation_already_converted')]);
            }

            return $this->invoices->createFromQuotation($quotation, $actor);
        });
    }

    public function delete(Quotation $quotation): void
    {
        DB::transaction(function () use ($quotation) {
            if (! $quotation->isDraft()) {
                throw ValidationException::withMessages(['delete' => __('messages.only_draft_deletable')]);
            }

            $number = $quotation->number;
            $quotation->delete();
            $this->activity->log('sales', 'quotation_deleted', 'Quotation '.$number.' deleted');
        });
    }

    private function calculate(array $data): array
    {
        $lines = collect($data['lines'])->map(fn ($line) => [
            'product_id' => $line['product_id'] ?? null,
            'description' => $line['description'],
            'quantity' => $line['quantity'],
            'unit_price_minor' => Money::toMinor($line['unit_price']),
            'tax_rate' => Money::percentToBasisPoints($line['tax_rate']),
        ])->all();

        return $this->calculator->calculate($lines, Money::toMinor($data['discount'] ?? '0'));
    }
}
