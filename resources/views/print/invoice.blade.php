{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.print')
@section('title', $invoice->number)
@section('content')
    @php($paid = (int) $invoice->payments->sum('amount_minor'))
    @php($balance = max(0, $invoice->total_minor - $paid))
    <header class="flex flex-col gap-6 border-b-2 border-brand-800 pb-6 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex items-start gap-4">
            @if(company()->logo_path)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url(company()->logo_path) }}" alt="{{ company()->displayName() }}" class="h-16 w-16 rounded-xl object-contain">
            @else
                <x-logo class="h-16 w-16" />
            @endif
            <div>
                <p class="text-xl font-bold text-brand-900" dir="auto">{{ company()->displayName() }}</p>
                @if(company()->displayAddress())<p class="mt-1 max-w-xs text-xs leading-5 text-brand-500" dir="auto">{{ company()->displayAddress() }}</p>@endif
                <p class="mt-1 text-xs text-brand-500">{{ company()->phone }}</p>
                <p class="text-xs text-brand-500">{{ company()->email }}</p>
                @if(company()->tax_number)<p class="text-xs text-brand-500">{{ __('fields.tax_number') }}: <span dir="ltr">{{ company()->tax_number }}</span></p>@endif
            </div>
        </div>
        <div class="text-start sm:text-end">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-accent-700">{{ __('invoices.print_title') }}</p>
            <p class="mt-1 font-mono text-xl font-bold text-brand-900" dir="ltr">{{ $invoice->number }}</p>
            <div class="mt-2"><x-status-badge :status="$invoice->status" /></div>
            <p class="mt-2 text-xs text-brand-500">{{ __('invoices.issue_date') }}: {{ $invoice->issue_date->format('Y-m-d') }}</p>
            @if($invoice->due_date)<p class="text-xs text-brand-500">{{ __('invoices.due_date') }}: {{ $invoice->due_date->format('Y-m-d') }}</p>@endif
        </div>
    </header>

    <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div class="rounded-xl bg-brand-50 p-4">
            <p class="text-[10px] font-bold uppercase tracking-widest text-brand-400">{{ __('invoices.invoice_for') }}</p>
            <p class="mt-2 font-bold text-brand-900" dir="auto">{{ $invoice->customer?->name }}</p>
            @if($invoice->customer?->company_name)<p class="text-sm text-brand-600" dir="auto">{{ $invoice->customer->company_name }}</p>@endif
            <p class="mt-1 text-xs text-brand-500">{{ $invoice->customer?->code }}</p>
            @if($invoice->customer?->email)<p class="text-xs text-brand-500" dir="ltr">{{ $invoice->customer->email }}</p>@endif
            @if($invoice->customer?->phone)<p class="text-xs text-brand-500" dir="ltr">{{ $invoice->customer->phone }}</p>@endif
            @if($invoice->customer?->address)<p class="mt-1 text-xs text-brand-500" dir="auto">{{ $invoice->customer->address }}</p>@endif
        </div>
        <div class="rounded-xl bg-brand-50 p-4 text-xs">
            <div class="flex justify-between gap-3 py-1"><span class="text-brand-400">{{ __('invoices.warehouse') }}</span><span class="font-medium text-brand-700">{{ $invoice->warehouse?->name ?? '—' }}</span></div>
            @if($invoice->quotation)<div class="flex justify-between gap-3 py-1"><span class="text-brand-400">{{ __('invoices.source_quotation') }}</span><span class="font-medium text-brand-700">{{ $invoice->quotation->number }}</span></div>@endif
            <div class="flex justify-between gap-3 py-1"><span class="text-brand-400">{{ __('common.created_by') }}</span><span class="font-medium text-brand-700">{{ $invoice->creator?->name ?? '—' }}</span></div>
            <div class="flex justify-between gap-3 py-1"><span class="text-brand-400">{{ __('fields.paid') }}</span><span class="font-medium text-positive-700">{{ money($paid) }}</span></div>
            <div class="flex justify-between gap-3 py-1"><span class="text-brand-400">{{ __('fields.balance') }}</span><span class="font-bold text-negative-600">{{ money($balance) }}</span></div>
        </div>
    </div>

    @if($invoice->isCancelled())
        <div class="mt-5 rounded-lg border border-rose-300 bg-rose-50 p-3 text-xs text-rose-800">
            <span class="font-bold">{{ __('statuses.cancelled') }}:</span> {{ $invoice->cancellation_reason }}
        </div>
    @endif

    <table class="mt-6 w-full text-sm">
        <thead>
            <tr class="border-b-2 border-grid text-start text-xs uppercase tracking-wider text-brand-500">
                <th class="py-2 text-start">{{ __('fields.description') }}</th>
                <th class="py-2 text-end">{{ __('fields.quantity') }}</th>
                <th class="py-2 text-end">{{ __('fields.unit_price') }}</th>
                <th class="py-2 text-end">{{ __('fields.tax') }}</th>
                <th class="py-2 text-end">{{ __('fields.total') }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($invoice->lines as $line)
            <tr class="border-b border-grid">
                <td class="py-3">
                    <p class="font-medium text-brand-800" dir="auto">{{ $line->description }}</p>
                    @if($line->product)<p class="text-[10px] text-brand-400">{{ $line->product->sku }}</p>@endif
                </td>
                <td class="py-3 text-end">{{ quantity($line->quantity) }}</td>
                <td class="py-3 text-end">{{ money($line->unit_price_minor) }}</td>
                <td class="py-3 text-end text-brand-500">{{ \App\Modules\Core\Services\Money::basisPointsToPercent($line->tax_rate) }}%</td>
                <td class="py-3 text-end font-semibold">{{ money($line->total_minor) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="mt-6 flex flex-col gap-6 sm:flex-row sm:justify-between">
        <div class="max-w-xs text-xs leading-6 text-brand-500">
            @if($invoice->notes)<p class="font-bold text-brand-700">{{ __('fields.notes') }}</p><p class="whitespace-pre-line" dir="auto">{{ $invoice->notes }}</p>@endif
            <p class="mt-4 font-bold text-brand-700">{{ __('invoices.stock_effects_hint') }}</p>
        </div>
        <dl class="w-full space-y-2 text-sm sm:max-w-xs">
            <div class="flex justify-between"><dt class="text-brand-500">{{ __('fields.subtotal') }}</dt><dd>{{ money($invoice->subtotal_minor) }}</dd></div>
            <div class="flex justify-between"><dt class="text-brand-500">{{ __('fields.discount') }}</dt><dd class="text-negative-600">− {{ money($invoice->discount_minor) }}</dd></div>
            <div class="flex justify-between"><dt class="text-brand-500">{{ __('fields.tax') }}</dt><dd>{{ money($invoice->tax_minor) }}</dd></div>
            <div class="flex justify-between border-t border-grid pt-2 text-base font-bold"><dt>{{ __('fields.total') }}</dt><dd class="text-brand-900">{{ money($invoice->total_minor) }}</dd></div>
            <div class="flex justify-between"><dt class="text-brand-500">{{ __('invoices.paid_amount') }}</dt><dd class="text-positive-700">{{ money($paid) }}</dd></div>
            <div class="flex justify-between rounded-lg bg-brand-50 px-3 py-2 text-base font-bold"><dt>{{ __('invoices.balance_due') }}</dt><dd>{{ money($balance) }}</dd></div>
        </dl>
    </div>

    <footer class="mt-10 border-t border-grid pt-4 text-center text-xs text-brand-400">
        <p>{{ __('invoices.thank_you') }}</p>
        <p class="mt-1">{{ company()->displayName() }} · {{ company()->phone }}</p>
    </footer>
@endsection
