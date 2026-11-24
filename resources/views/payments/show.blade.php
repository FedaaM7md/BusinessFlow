{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', $payment->number)
@section('content')
    <x-page-header :title="__('payments.receipt')" :subtitle="$payment->number">
        <x-slot:actions>
            <a href="{{ route('payments.index') }}" class="btn-secondary">{{ __('common.back') }}</a>
            @can('invoices.view')<a href="{{ route('invoices.show', $payment->invoice) }}" class="btn-primary">{{ __('invoices.print_title') }}</a>@endcan
        </x-slot:actions>
    </x-page-header>

    <div class="mx-auto max-w-2xl">
        <div class="card overflow-hidden">
            <div class="bg-gradient-to-br from-brand-900 to-brand-800 px-6 py-8 text-white">
                <div class="flex items-start justify-between gap-4">
                    <div><p class="text-xs font-semibold uppercase tracking-widest text-brand-200">{{ __('payments.receipt') }}</p><p class="mt-2 text-2xl font-bold" dir="ltr">{{ $payment->number }}</p></div>
                    <x-logo class="h-12 w-12" />
                </div>
                <p class="mt-6 text-4xl font-bold" dir="ltr">{{ money($payment->amount_minor) }}</p>
                <p class="mt-1 text-xs text-brand-200">{{ $payment->payment_date->format('Y-m-d') }} · {{ __('statuses.'.$payment->method) }}</p>
            </div>
            <div class="divide-y divide-slate-100 px-6">
                <div class="flex justify-between gap-4 py-4 text-sm"><span class="text-slate-500">{{ __('fields.customer') }}</span><a href="{{ route('customers.show', $payment->customer) }}" class="font-semibold text-brand-800 hover:underline">{{ $payment->customer?->name }}</a></div>
                <div class="flex justify-between gap-4 py-4 text-sm"><span class="text-slate-500">{{ __('payments.applied_to') }}</span><a href="{{ route('invoices.show', $payment->invoice) }}" class="font-mono font-semibold text-brand-800 hover:underline">{{ $payment->invoice?->number }}</a></div>
                <div class="flex justify-between gap-4 py-4 text-sm"><span class="text-slate-500">{{ __('fields.reference') }}</span><span class="font-medium text-slate-700">{{ $payment->reference ?: '—' }}</span></div>
                <div class="flex justify-between gap-4 py-4 text-sm"><span class="text-slate-500">{{ __('payments.recorded_by') }}</span><span class="font-medium text-slate-700">{{ $payment->creator?->name ?? '—' }}</span></div>
                @if($payment->notes)<div class="py-4 text-sm"><span class="block text-slate-500">{{ __('fields.notes') }}</span><span class="mt-1 block whitespace-pre-line text-slate-700">{{ $payment->notes }}</span></div>@endif
            </div>
        </div>
        <p class="mt-4 text-center text-xs leading-6 text-slate-400">{{ __('payments.immutable_note') }}</p>
    </div>
@endsection
