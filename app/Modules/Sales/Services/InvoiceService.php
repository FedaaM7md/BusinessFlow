<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Sales\Services;

use App\Models\User;
use App\Modules\Core\Services\ActivityLogger;
use App\Modules\Core\Services\DocumentNumberService;
use App\Modules\Core\Services\Money;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\InventoryService;
use App\Modules\Sales\Models\Invoice;
use App\Modules\Sales\Models\Quotation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceService
{
    public function __construct(
        private readonly ActivityLogger $activity,
        private readonly DocumentNumberService $numbers,
        private readonly DocumentCalculator $calculator,
        private readonly InventoryService $inventory,
    ) {}

    public function create(array $data, User $actor): Invoice
    {
        return DB::transaction(function () use ($data, $actor) {
            $calculated = $this->calculate($data);

            return $this->persist($data, $calculated, $actor);
        });
    }

    public function update(Invoice $invoice, array $data): Invoice
    {
        return DB::transaction(function () use ($invoice, $data) {
            if (! $invoice->isDraft()) {
                throw ValidationException::withMessages(['status' => __('messages.only_draft_editable')]);
            }

            $calculated = $this->calculate($data);
            $invoice->update([
                'customer_id' => $data['customer_id'],
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'] ?? null,
                'subtotal_minor' => $calculated['subtotal_minor'],
                'discount_minor' => $calculated['discount_minor'],
                'tax_minor' => $calculated['tax_minor'],
                'total_minor' => $calculated['total_minor'],
                'notes' => $data['notes'] ?? null,
            ]);

            $invoice->lines()->delete();
            $invoice->lines()->createMany($calculated['lines']);

            $this->activity->log('sales', 'invoice_updated', 'Invoice '.$invoice->number.' updated', $invoice);

            return $invoice->refresh()->load('lines');
        });
    }

    public function createFromQuotation(Quotation $quotation, User $actor): Invoice
    {
        return DB::transaction(function () use ($quotation, $actor) {
            $quotation->loadMissing('lines');
            $lines = $quotation->lines->map(fn ($line) => [
                'product_id' => $line->product_id,
                'description' => $line->description,
                'quantity' => $line->quantity,
                'unit_price_minor' => $line->unit_price_minor,
                'tax_rate' => $line->tax_rate,
            ])->all();

            $calculated = $this->calculator->calculate($lines, $quotation->discount_minor);

            $invoice = Invoice::create([
                'number' => $this->numbers->next('invoice', now()),
                'customer_id' => $quotation->customer_id,
                'quotation_id' => $quotation->id,
                'warehouse_id' => Warehouse::default()->id,
                'status' => 'draft',
                'issue_date' => now()->format('Y-m-d'),
                'due_date' => now()->addDays(30)->format('Y-m-d'),
                'currency_code' => $quotation->currency_code,
                'subtotal_minor' => $calculated['subtotal_minor'],
                'discount_minor' => $calculated['discount_minor'],
                'tax_minor' => $calculated['tax_minor'],
                'total_minor' => $calculated['total_minor'],
                'notes' => $quotation->notes,
                'created_by' => $actor->id,
            ]);
            $invoice->lines()->createMany($calculated['lines']);

            $this->activity->log('sales', 'invoice_created', 'Invoice '.$invoice->number.' created from quotation '.$quotation->number, $invoice, [
                'total_minor' => ['from' => null, 'to' => $invoice->total_minor],
            ]);

            return $invoice->load('lines');
        });
    }

    public function issue(Invoice $invoice, User $actor): Invoice
    {
        return DB::transaction(function () use ($invoice, $actor) {
            $invoice->refresh()->load('lines.product');

            if (! $invoice->canIssue()) {
                throw ValidationException::withMessages(['status' => __('messages.invoice_cannot_issue')]);
            }

            if ($invoice->lines->isEmpty()) {
                throw ValidationException::withMessages(['status' => __('messages.invoice_needs_lines')]);
            }

            $this->inventory->issueInvoice($invoice, $actor);
            $invoice->update(['status' => 'issued']);
            $this->activity->log('sales', 'invoice_issued', 'Invoice '.$invoice->number.' issued', $invoice, [
                'status' => ['from' => 'draft', 'to' => 'issued'],
            ]);

            return $invoice->refresh();
        });
    }

    public function cancel(Invoice $invoice, string $reason, User $actor): Invoice
    {
        return DB::transaction(function () use ($invoice, $reason, $actor) {
            $invoice->refresh();

            if (! $invoice->canCancel()) {
                throw ValidationException::withMessages(['status' => __('messages.invoice_cannot_cancel')]);
            }

            $from = $invoice->status;
            $this->inventory->reverseInvoice($invoice, $actor);
            $invoice->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => $actor->id,
                'cancellation_reason' => $reason,
            ]);
            $this->activity->log('sales', 'invoice_cancelled', 'Invoice '.$invoice->number.' cancelled: '.$reason, $invoice, [
                'status' => ['from' => $from, 'to' => 'cancelled'],
            ]);

            return $invoice->refresh();
        });
    }

    public function delete(Invoice $invoice): void
    {
        DB::transaction(function () use ($invoice) {
            if (! $invoice->isDraft()) {
                throw ValidationException::withMessages(['delete' => __('messages.issued_invoice_not_deletable')]);
            }

            $number = $invoice->number;
            $invoice->delete();
            $this->activity->log('sales', 'invoice_deleted', 'Draft invoice '.$number.' deleted');
        });
    }

    public function syncPaymentStatus(Invoice $invoice): Invoice
    {
        if ($invoice->status === 'cancelled' || $invoice->status === 'draft') {
            return $invoice;
        }

        $paid = $invoice->paidMinor();
        $newStatus = $paid <= 0
            ? 'issued'
            : ($paid >= $invoice->total_minor ? 'paid' : 'partially_paid');

        if ($newStatus !== $invoice->status) {
            $from = $invoice->status;
            $invoice->update(['status' => $newStatus]);
            $this->activity->log('sales', 'invoice_status_changed', 'Invoice '.$invoice->number.' changed to '.$newStatus, $invoice, [
                'status' => ['from' => $from, 'to' => $newStatus],
            ]);
        }

        return $invoice->refresh();
    }

    private function persist(array $data, array $calculated, User $actor): Invoice
    {
        $invoice = Invoice::create([
            'number' => $this->numbers->next('invoice', $data['issue_date']),
            'customer_id' => $data['customer_id'],
            'warehouse_id' => $data['warehouse_id'] ?? Warehouse::default()->id,
            'status' => 'draft',
            'issue_date' => $data['issue_date'],
            'due_date' => $data['due_date'] ?? null,
            'currency_code' => company()->currency_code,
            'subtotal_minor' => $calculated['subtotal_minor'],
            'discount_minor' => $calculated['discount_minor'],
            'tax_minor' => $calculated['tax_minor'],
            'total_minor' => $calculated['total_minor'],
            'notes' => $data['notes'] ?? null,
            'created_by' => $actor->id,
        ]);
        $invoice->lines()->createMany($calculated['lines']);

        $this->activity->log('sales', 'invoice_created', 'Invoice '.$invoice->number.' created', $invoice, [
            'total_minor' => ['from' => null, 'to' => $invoice->total_minor],
        ]);

        return $invoice->load('lines');
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
