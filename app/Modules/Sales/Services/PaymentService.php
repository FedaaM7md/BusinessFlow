<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Sales\Services;

use App\Models\User;
use App\Modules\Core\Services\ActivityLogger;
use App\Modules\Core\Services\DocumentNumberService;
use App\Modules\Core\Services\Money;
use App\Modules\Sales\Models\Invoice;
use App\Modules\Sales\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(
        private readonly ActivityLogger $activity,
        private readonly DocumentNumberService $numbers,
        private readonly InvoiceService $invoices,
    ) {}

    public function create(array $data, User $actor): Payment
    {
        return DB::transaction(function () use ($data, $actor) {
            $invoice = Invoice::query()->lockForUpdate()->findOrFail($data['invoice_id']);

            if (! $invoice->canAcceptPayments()) {
                throw ValidationException::withMessages(['invoice_id' => __('messages.invoice_not_payable')]);
            }

            $amount = Money::toMinor($data['amount']);
            $balance = $invoice->balanceMinor();

            if ($amount <= 0) {
                throw ValidationException::withMessages(['amount' => __('messages.amount_positive')]);
            }

            if ($amount > $balance) {
                throw ValidationException::withMessages([
                    'amount' => __('messages.payment_exceeds_balance', ['balance' => money($balance)]),
                ]);
            }

            $payment = Payment::create([
                'number' => $this->numbers->next('payment', $data['payment_date']),
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'amount_minor' => $amount,
                'method' => $data['method'],
                'payment_date' => $data['payment_date'],
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $actor->id,
            ]);

            $this->invoices->syncPaymentStatus($invoice);

            $this->activity->log('payments', 'payment_created', 'Payment '.$payment->number.' recorded for invoice '.$invoice->number, $payment, [
                'amount_minor' => ['from' => null, 'to' => $amount],
            ]);

            return $payment;
        });
    }
}
