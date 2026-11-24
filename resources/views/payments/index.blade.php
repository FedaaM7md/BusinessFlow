{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', __('payments.title'))
@section('content')
    <x-page-header :title="__('payments.title')" :subtitle="__('payments.subtitle')">
        <x-slot:actions>@can('payments.manage')<a href="{{ route('payments.create') }}" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> {{ __('payments.create') }}</a>@endcan</x-slot:actions>
    </x-page-header>

    <x-filter-panel>
        <form method="GET" action="{{ route('payments.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-[1fr_170px_150px_150px_auto]">
            <div class="relative"><span class="pointer-events-none absolute inset-y-0 start-3 flex items-center text-slate-400"><x-icon name="search" class="h-4 w-4" /></span><input type="search" name="q" value="{{ request('q') }}" class="input ps-10" placeholder="{{ __('payments.search_placeholder') }}"></div>
            <select name="method" class="select"><option value="">{{ __('payments.all_methods') }}</option>@foreach(\App\Modules\Sales\Models\Payment::METHODS as $method)<option value="{{ $method }}" @selected(request('method') === $method)>{{ __('statuses.'.$method) }}</option>@endforeach</select>
            <x-date-input name="from" :value="request('from')" />
            <x-date-input name="to" :value="request('to')" />
            <div class="flex gap-2"><button class="btn-primary">{{ __('common.apply') }}</button><a href="{{ route('payments.index') }}" class="btn-ghost">{{ __('common.reset') }}</a></div>
        </form>
    </x-filter-panel>

    <div class="card overflow-hidden">
        <div class="divide-y divide-grid md:hidden">
            @forelse($payments as $payment)
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <a href="{{ route('payments.show', $payment) }}" class="code text-sm font-bold text-brand-800">{{ $payment->number }}</a>
                        <span class="badge bg-brand-50 text-brand-600 ring-brand-500/10">{{ __('statuses.'.$payment->method) }}</span>
                    </div>
                    <p class="mt-1 break-words text-sm font-semibold text-brand-800">{{ $payment->customer?->name }}</p>
                    <dl class="mt-2 grid grid-cols-2 gap-2 text-xs text-brand-500">
                        <div><dt class="text-[10px] text-brand-400">{{ __('fields.date') }}</dt><dd class="amount text-brand-700">{{ $payment->payment_date->format('Y-m-d') }}</dd></div>
                        <div class="text-end"><dt class="text-[10px] text-brand-400">{{ __('fields.amount') }}</dt><dd class="amount font-bold text-positive-700">{{ money($payment->amount_minor) }}</dd></div>
                    </dl>
                    <div class="mt-2 flex items-center justify-between gap-2 text-xs text-brand-500">
                        <a href="{{ route('invoices.show', $payment->invoice) }}" class="code text-brand-600 hover:underline">{{ $payment->invoice?->number }}</a>
                        <a href="{{ route('payments.show', $payment) }}" class="icon-btn" title="{{ __('common.view') }}"><x-icon name="eye" class="h-4 w-4" /></a>
                    </div>
                </div>
            @empty
                <x-empty-state icon="payments" :title="__('common.no_results')" :description="__('payments.subtitle')" />
            @endforelse
        </div>
        <div class="hidden md:block">
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>{{ __('fields.code') }}</th><th>{{ __('fields.date') }}</th><th>{{ __('fields.customer') }}</th><th>{{ __('fields.invoice') }}</th><th>{{ __('fields.method') }}</th><th>{{ __('fields.reference') }}</th><th class="text-end">{{ __('fields.amount') }}</th><th></th></tr></thead>
                <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td><a href="{{ route('payments.show', $payment) }}" class="font-mono text-xs font-bold text-brand-800 hover:underline">{{ $payment->number }}</a></td>
                        <td class="whitespace-nowrap text-slate-500">{{ $payment->payment_date->format('Y-m-d') }}</td>
                        <td class="font-medium text-slate-700">{{ $payment->customer?->name }}</td>
                        <td><a href="{{ route('invoices.show', $payment->invoice) }}" class="font-mono text-xs text-slate-600 hover:text-brand-800 hover:underline">{{ $payment->invoice?->number }}</a></td>
                        <td><span class="badge bg-slate-100 text-slate-600 ring-slate-500/10">{{ __('statuses.'.$payment->method) }}</span></td>
                        <td class="text-slate-500">{{ $payment->reference ?: '—' }}</td>
                        <td class="whitespace-nowrap text-end font-bold text-emerald-700">{{ money($payment->amount_minor) }}</td>
                        <td class="text-end"><a href="{{ route('payments.show', $payment) }}" class="icon-btn" title="{{ __('common.view') }}"><x-icon name="eye" class="h-4 w-4" /></a></td>
                    </tr>
                @empty
                    <tr><td colspan="8"><x-empty-state icon="payments" :title="__('common.no_results')" :description="__('payments.subtitle')" /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        </div>
        <x-pagination :paginator="$payments" />
    </div>
@endsection
