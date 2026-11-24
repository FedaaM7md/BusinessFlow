{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', $invoice->number)
@section('content')
    @php
        $paid = (int) $invoice->payments->sum('amount_minor');
        $balance = max(0, $invoice->total_minor - $paid);
    @endphp
    <x-page-header :title="$invoice->number" :subtitle="__('invoices.invoice_for').' '.$invoice->customer?->name">
        <x-slot:actions>
            <x-status-badge :status="$invoice->status" />
            <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="btn-secondary"><x-icon name="print" class="h-4 w-4" /> {{ __('common.print') }}</a>
            @if($invoice->isDraft())
                @can('invoices.manage')
                    <a href="{{ route('invoices.edit', $invoice) }}" class="btn-secondary"><x-icon name="edit" class="h-4 w-4" /> {{ __('common.edit') }}</a>
                    <form method="POST" action="{{ route('invoices.issue', $invoice) }}" data-confirm="{{ __('invoices.issue_confirm') }}">@csrf<button class="btn-primary">{{ __('invoices.issue') }}</button></form>
                @endcan
            @endif
            @if($invoice->canAcceptPayments())
                @can('payments.manage')<a href="{{ route('payments.create', ['invoice' => $invoice->id]) }}" class="btn-primary">{{ __('invoices.record_payment') }}</a>@endcan
            @endif
            <a href="{{ route('invoices.index') }}" class="btn-ghost">{{ __('common.back') }}</a>
        </x-slot:actions>
    </x-page-header>

    @if($invoice->isCancelled())
        <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
            <p class="font-bold">{{ __('invoices.cancel_invoice') }} · {{ $invoice->cancelled_at?->format('Y-m-d H:i') }}</p>
            <p class="mt-1">{{ $invoice->cancellation_reason }}</p>
            <p class="mt-1 text-xs text-rose-600">{{ __('invoices.cancelled_by') }}: {{ $invoice->canceller?->name ?? '—' }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <section class="card overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                    <div><h2 class="section-title">{{ __('invoices.lines') }}</h2><p class="muted mt-0.5">{{ __('invoices.issued_date', ['date' => $invoice->issue_date->format('Y-m-d')]) }} @if($invoice->due_date) · {{ __('invoices.due_by', ['date' => $invoice->due_date->format('Y-m-d')]) }} @endif</p></div>
                    @if($invoice->isOverdue())<span class="badge bg-rose-50 text-rose-700 ring-rose-600/20">{{ __('invoices.overdue') }}</span>@endif
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead><tr><th>#</th><th>{{ __('fields.description') }}</th><th class="text-end">{{ __('fields.quantity') }}</th><th class="text-end">{{ __('fields.unit_price') }}</th><th class="text-end">{{ __('fields.tax') }}</th><th class="text-end">{{ __('fields.total') }}</th></tr></thead>
                        <tbody>
                        @foreach($invoice->lines as $line)
                            <tr>
                                <td class="text-slate-400">{{ $loop->iteration }}</td>
                                <td><p class="font-medium text-slate-700">{{ $line->description }}</p>@if($line->product)<p class="text-[11px] text-slate-400">{{ $line->product->sku }}</p>@endif</td>
                                <td class="text-end">{{ quantity($line->quantity) }}</td>
                                <td class="text-end">{{ money($line->unit_price_minor) }}</td>
                                <td class="text-end text-slate-500">{{ \App\Modules\Core\Services\Money::basisPointsToPercent($line->tax_rate) }}%</td>
                                <td class="text-end font-semibold">{{ money($line->total_minor) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="flex justify-end border-t border-slate-100 p-5">
                    <dl class="w-full max-w-sm space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-slate-500">{{ __('fields.subtotal') }}</dt><dd>{{ money($invoice->subtotal_minor) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">{{ __('fields.discount') }}</dt><dd class="text-rose-600">− {{ money($invoice->discount_minor) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">{{ __('fields.tax') }}</dt><dd>{{ money($invoice->tax_minor) }}</dd></div>
                        <div class="flex justify-between border-t border-slate-200 pt-2 text-base font-bold"><dt>{{ __('fields.total') }}</dt><dd class="text-brand-800">{{ money($invoice->total_minor) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">{{ __('invoices.paid_amount') }}</dt><dd class="text-emerald-700">{{ money($paid) }}</dd></div>
                        <div class="flex justify-between text-base font-bold"><dt>{{ __('invoices.balance_due') }}</dt><dd class="{{ $balance > 0 ? 'text-rose-600' : 'text-emerald-700' }}">{{ money($balance) }}</dd></div>
                    </dl>
                </div>
            </section>

            <section class="card overflow-hidden">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <h2 class="section-title">{{ __('invoices.payment_history') }}</h2>
                    @if($invoice->canAcceptPayments())@can('payments.manage')<a href="{{ route('payments.create', ['invoice' => $invoice->id]) }}" class="btn-secondary !px-3 !py-2 text-xs">{{ __('invoices.record_payment') }}</a>@endcan @endif
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead><tr><th>{{ __('fields.code') }}</th><th>{{ __('fields.date') }}</th><th>{{ __('fields.method') }}</th><th>{{ __('fields.reference') }}</th><th class="text-end">{{ __('fields.amount') }}</th></tr></thead>
                        <tbody>
                        @forelse($invoice->payments as $payment)
                            <tr>
                                <td><a href="{{ route('payments.show', $payment) }}" class="font-mono text-xs font-bold text-brand-800 hover:underline">{{ $payment->number }}</a></td>
                                <td>{{ $payment->payment_date->format('Y-m-d') }}</td>
                                <td>{{ __('statuses.'.$payment->method) }}</td>
                                <td class="text-slate-500">{{ $payment->reference ?: '—' }}</td>
                                <td class="text-end font-semibold text-emerald-700">{{ money($payment->amount_minor) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="px-5 py-8 text-center text-sm text-slate-500">{{ __('invoices.no_payments') }}</div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="card p-5">
                <h2 class="section-title">{{ __('fields.customer') }}</h2>
                <div class="mt-3 space-y-1 text-sm">
                    <a href="{{ route('customers.show', $invoice->customer) }}" class="font-bold text-slate-800 hover:text-brand-800">{{ $invoice->customer?->name }}</a>
                    <p class="text-xs text-slate-400">{{ $invoice->customer?->code }}</p>
                    @if($invoice->customer?->email)<p class="text-xs text-slate-500">{{ $invoice->customer->email }}</p>@endif
                    @if($invoice->customer?->phone)<p class="text-xs text-slate-500" dir="ltr">{{ $invoice->customer->phone }}</p>@endif
                </div>
                <dl class="mt-5 space-y-3 border-t border-slate-100 pt-4 text-xs">
                    <div class="flex justify-between gap-2"><dt class="text-slate-400">{{ __('invoices.warehouse') }}</dt><dd class="font-medium text-slate-600">{{ $invoice->warehouse?->name ?? '—' }}</dd></div>
                    @if($invoice->quotation)
                        <div class="flex justify-between gap-2"><dt class="text-slate-400">{{ __('invoices.source_quotation') }}</dt><dd><a href="{{ route('quotations.show', $invoice->quotation) }}" class="font-semibold text-brand-800 hover:underline">{{ $invoice->quotation->number }}</a></dd></div>
                    @endif
                    <div class="flex justify-between gap-2"><dt class="text-slate-400">{{ __('common.created_by') }}</dt><dd class="font-medium text-slate-600">{{ $invoice->creator?->name ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-2"><dt class="text-slate-400">{{ __('common.created_at') }}</dt><dd class="font-medium text-slate-600">{{ $invoice->created_at->format('Y-m-d') }}</dd></div>
                </dl>
            </section>

            @if($invoice->canCancel())
                @can('invoices.manage')
                    <section class="card p-5" x-data="{ open: false }">
                        <h2 class="section-title">{{ __('invoices.cancel') }}</h2>
                        <p class="mt-1 text-xs leading-5 text-slate-500">{{ __('invoices.cancel_warning') }}</p>
                        <button type="button" class="btn-danger mt-4 w-full" @click="open = !open">{{ __('invoices.cancel_invoice') }}</button>
                        <form x-show="open" x-cloak method="POST" action="{{ route('invoices.cancel', $invoice) }}" class="mt-4 space-y-3" data-confirm="{{ __('invoices.cancel_confirm') }}">
                            @csrf
                            <div>
                                <label for="cancellation_reason" class="label">{{ __('fields.cancellation_reason') }}</label>
                                <textarea id="cancellation_reason" name="cancellation_reason" rows="3" class="input" required minlength="5">{{ old('cancellation_reason') }}</textarea>
                                <p class="mt-1 text-[11px] text-slate-400">{{ __('invoices.cancel_reason_hint') }}</p>
                                @error('cancellation_reason')<p class="error-text">{{ $message }}</p>@enderror
                            </div>
                            <button type="submit" class="btn-danger w-full">{{ __('invoices.cancel_confirm') }}</button>
                        </form>
                    </section>
                @endcan
            @endif

            @if($invoice->isDraft())
                <form method="POST" action="{{ route('invoices.destroy', $invoice) }}" data-confirm="{{ __('common.confirm_delete_text') }}">
                    @csrf @method('DELETE')
                    <button class="btn-ghost w-full !text-rose-600"><x-icon name="trash" class="h-4 w-4" /> {{ __('common.delete') }}</button>
                </form>
            @endif
        </aside>
    </div>
@endsection
