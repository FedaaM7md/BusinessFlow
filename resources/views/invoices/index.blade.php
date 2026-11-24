{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', __('invoices.title'))
@section('content')
    <x-page-header :title="__('invoices.title')" :subtitle="__('invoices.subtitle')">
        <x-slot:actions>@can('invoices.manage')<a href="{{ route('invoices.create') }}" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> {{ __('invoices.create') }}</a>@endcan</x-slot:actions>
    </x-page-header>

    <x-filter-panel>
        <form method="GET" action="{{ route('invoices.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-[1fr_160px_190px_150px_150px_auto]">
            <div class="relative"><span class="pointer-events-none absolute inset-y-0 start-3 flex items-center text-slate-400"><x-icon name="search" class="h-4 w-4" /></span><input type="search" name="q" value="{{ request('q') }}" class="input ps-10" placeholder="{{ __('invoices.search_placeholder') }}"></div>
            <select name="status" class="select"><option value="">{{ __('invoices.all_statuses') }}</option>@foreach(['draft','issued','partially_paid','paid','cancelled'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ __('statuses.'.$status) }}</option>@endforeach</select>
            <select name="customer" class="select"><option value="">{{ __('invoices.all_customers') }}</option>@foreach($customers as $customer)<option value="{{ $customer->id }}" @selected((string) request('customer') === (string) $customer->id)>{{ $customer->name }}</option>@endforeach</select>
            <x-date-input name="from" :value="request('from')" />
            <x-date-input name="to" :value="request('to')" />
            <div class="flex flex-wrap items-center gap-2">
                <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2.5 text-xs font-medium text-slate-600"><input type="checkbox" name="overdue" value="1" @checked(request('overdue') === '1') class="h-4 w-4 rounded border-slate-300 text-brand-800">{{ __('invoices.overdue_only') }}</label>
                <button class="btn-primary">{{ __('common.apply') }}</button>
                <a href="{{ route('invoices.index') }}" class="btn-ghost">{{ __('common.reset') }}</a>
            </div>
        </form>
    </x-filter-panel>

    <div class="card overflow-hidden">
        <div class="divide-y divide-grid md:hidden">
            @forelse($invoices as $invoice)
                @php($paid = (int) ($invoice->paid_total ?? 0))
                @php($balance = max(0, $invoice->total_minor - $paid))
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <a href="{{ route('invoices.show', $invoice) }}" class="code text-sm font-bold text-brand-800">{{ $invoice->number }}</a>
                        <x-status-badge :status="$invoice->status" />
                    </div>
                    <p class="mt-1 break-words text-sm font-semibold text-brand-800">{{ $invoice->customer?->name }}</p>
                    <dl class="mt-2 grid grid-cols-2 gap-2 text-xs text-brand-500">
                        <div><dt class="text-[10px] text-brand-400">{{ __('invoices.issue_date') }}</dt><dd class="amount text-brand-700">{{ $invoice->issue_date->format('Y-m-d') }}</dd></div>
                        <div class="text-end"><dt class="text-[10px] text-brand-400">{{ __('fields.total') }}</dt><dd class="amount font-bold text-brand-900">{{ money($invoice->total_minor) }}</dd></div>
                    </dl>
                    <div class="mt-3 flex items-center justify-between gap-2">
                        <span class="text-xs {{ $balance > 0 ? 'text-negative-600' : 'text-brand-400' }}">{{ __('fields.balance') }}: <span class="amount">{{ money($balance) }}</span></span>
                        <div class="flex items-center gap-1">
                            <a href="{{ route('invoices.show', $invoice) }}" class="icon-btn" title="{{ __('common.view') }}"><x-icon name="eye" class="h-4 w-4" /></a>
                            <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="icon-btn" title="{{ __('common.print') }}"><x-icon name="print" class="h-4 w-4" /></a>
                            @if($invoice->canAcceptPayments())
                                @can('payments.manage')<a href="{{ route('payments.create', ['invoice' => $invoice->id]) }}" class="icon-btn" title="{{ __('invoices.record_payment') }}"><x-icon name="payments" class="h-4 w-4" /></a>@endcan
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <x-empty-state icon="invoices" :title="__('common.no_results')" :description="__('invoices.subtitle')" />
            @endforelse
        </div>
        <div class="hidden md:block">
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr>
                    <x-sortable-th column="number" :sort="$sort" :direction="$direction">{{ __('fields.number') }}</x-sortable-th>
                    <th>{{ __('fields.customer') }}</th>
                    <x-sortable-th column="issue_date" :sort="$sort" :direction="$direction">{{ __('invoices.issue_date') }}</x-sortable-th>
                    <x-sortable-th column="due_date" :sort="$sort" :direction="$direction">{{ __('invoices.due_date') }}</x-sortable-th>
                    <x-sortable-th column="total_minor" :sort="$sort" :direction="$direction">{{ __('fields.total') }}</x-sortable-th>
                    <th class="text-end">{{ __('fields.paid') }}</th>
                    <th class="text-end">{{ __('fields.balance') }}</th>
                    <x-sortable-th column="status" :sort="$sort" :direction="$direction">{{ __('common.status') }}</x-sortable-th>
                    <th class="text-end">{{ __('common.actions') }}</th>
                </tr></thead>
                <tbody>
                @forelse($invoices as $invoice)
                    @php($paid = (int) ($invoice->paid_total ?? 0))
                    @php($balance = max(0, $invoice->total_minor - $paid))
                    <tr>
                        <td><a href="{{ route('invoices.show', $invoice) }}" class="font-mono text-xs font-bold text-brand-800 hover:underline">{{ $invoice->number }}</a>@if($invoice->isOverdue())<span class="badge ms-2 bg-rose-50 text-rose-700 ring-rose-600/20">{{ __('statuses.overdue') }}</span>@endif</td>
                        <td><p class="font-semibold text-slate-700">{{ $invoice->customer?->name }}</p><p class="text-[11px] text-slate-400">{{ $invoice->customer?->company_name }}</p></td>
                        <td class="whitespace-nowrap text-slate-500">{{ $invoice->issue_date->format('Y-m-d') }}</td>
                        <td class="whitespace-nowrap {{ $invoice->isOverdue() ? 'font-semibold text-rose-600' : 'text-slate-500' }}">{{ $invoice->due_date?->format('Y-m-d') ?? '—' }}</td>
                        <td class="whitespace-nowrap font-bold text-slate-800">{{ money($invoice->total_minor) }}</td>
                        <td class="whitespace-nowrap text-end text-emerald-700">{{ $paid ? money($paid) : '—' }}</td>
                        <td class="whitespace-nowrap text-end font-semibold {{ $balance > 0 ? 'text-rose-600' : 'text-slate-400' }}">{{ money($balance) }}</td>
                        <td><x-status-badge :status="$invoice->status" /></td>
                        <td><div class="flex items-center justify-end gap-1">
                            <a href="{{ route('invoices.show', $invoice) }}" class="icon-btn" title="{{ __('common.view') }}"><x-icon name="eye" class="h-4 w-4" /></a>
                            <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="icon-btn" title="{{ __('common.print') }}"><x-icon name="print" class="h-4 w-4" /></a>
                            @if($invoice->canAcceptPayments())
                                @can('payments.manage')<a href="{{ route('payments.create', ['invoice' => $invoice->id]) }}" class="icon-btn" title="{{ __('invoices.record_payment') }}"><x-icon name="payments" class="h-4 w-4" /></a>@endcan
                            @endif
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="9"><x-empty-state icon="invoices" :title="__('common.no_results')" :description="__('invoices.subtitle')" /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        </div>
        <x-pagination :paginator="$invoices" />
    </div>
@endsection
