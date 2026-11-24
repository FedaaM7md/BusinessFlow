{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', __('reports.title'))
@section('content')
    <x-page-header :title="__('reports.title')" :subtitle="__('reports.subtitle')" />

    <x-filter-panel class="mb-6">
        <form method="GET" action="{{ route('reports.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="flex-1">
                <label class="label" for="from">{{ __('reports.date_range') }}</label>
                <div class="flex items-center gap-2">
                    <x-date-input id="from" name="from" :value="$from->format('Y-m-d')" />
                    <span class="text-slate-400">—</span>
                    <x-date-input name="to" :value="$to->format('Y-m-d')" />
                </div>
            </div>
            <button class="btn-primary">{{ __('reports.apply_range') }}</button>
            <a href="{{ route('reports.index') }}" class="btn-ghost">{{ __('common.reset') }}</a>
            @foreach(['sales' => 'reports.export_sales', 'aging' => 'reports.export_aging', 'expenses' => 'reports.export_expenses', 'stock' => 'reports.export_stock'] as $type => $label)
                <a href="{{ route('reports.export', ['type' => $type, 'from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}" class="btn-secondary hidden xl:inline-flex"><x-icon name="download" class="h-4 w-4" /> {{ __($label) }}</a>
            @endforeach
        </form>
    </x-filter-panel>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="stat-card"><p class="kpi-label">{{ __('reports.total_sales') }}</p><p class="kpi-value">{{ money($summary['sales']) }}</p></div>
        <div class="stat-card"><p class="kpi-label">{{ __('reports.total_expenses') }}</p><p class="kpi-value">{{ money($summary['expenses']) }}</p></div>
        <div class="stat-card"><p class="kpi-label">{{ __('reports.net_result') }}</p><p class="kpi-value {{ $summary['profit'] < 0 ? '!text-rose-600' : '!text-emerald-700' }}">{{ money($summary['profit']) }}</p></div>
        <div class="stat-card"><p class="kpi-label">{{ __('reports.receivables') }}</p><p class="kpi-value">{{ money($summary['receivables']) }}</p></div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
        <section class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div><h2 class="section-title">{{ __('reports.sales_by_period') }}</h2><p class="muted mt-0.5">{{ __('reports.from_to', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}</p></div>
                @can('reports.view')<a href="{{ route('reports.export', ['type' => 'sales', 'from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}" class="icon-btn" title="{{ __('reports.export_sales') }}"><x-icon name="download" class="h-4 w-4" /></a>@endcan
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>{{ __('reports.period') }}</th><th class="text-end">{{ __('reports.invoices') }}</th><th class="text-end">{{ __('fields.subtotal') }}</th><th class="text-end">{{ __('fields.tax') }}</th><th class="text-end">{{ __('fields.total') }}</th></tr></thead>
                    <tbody>
                    @forelse($salesByPeriod as $row)
                        <tr><td class="font-mono text-xs">{{ $row['period'] }}</td><td class="text-end">{{ $row['invoices'] }}</td><td class="text-end">{{ money($row['subtotal']) }}</td><td class="text-end text-slate-500">{{ money($row['tax']) }}</td><td class="text-end font-bold">{{ money($row['total']) }}</td></tr>
                    @empty
                        <tr><td colspan="5"><x-empty-state icon="reports" :title="__('reports.no_data')" :description="__('reports.date_range')" /></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div><h2 class="section-title">{{ __('reports.expenses_by_category') }}</h2><p class="muted mt-0.5">{{ __('reports.from_to', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}</p></div>
                @can('reports.view')<a href="{{ route('reports.export', ['type' => 'expenses', 'from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}" class="icon-btn" title="{{ __('reports.export_expenses') }}"><x-icon name="download" class="h-4 w-4" /></a>@endcan
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>{{ __('fields.category') }}</th><th class="text-end">{{ __('reports.count') }}</th><th class="text-end">{{ __('fields.amount') }}</th></tr></thead>
                    <tbody>
                    @forelse($expenses as $row)
                        <tr><td>{{ $row->category?->displayName() ?? '—' }}</td><td class="text-end text-slate-500">{{ $row->count }}</td><td class="text-end font-bold">{{ money((int) $row->total) }}</td></tr>
                    @empty
                        <tr><td colspan="3"><x-empty-state icon="expenses" :title="__('reports.no_data')" :description="__('reports.date_range')" /></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <section class="card mt-6 overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
            <div><h2 class="section-title">{{ __('reports.receivables_aging') }}</h2><p class="muted mt-0.5">{{ __('reports.aging_note') }}</p></div>
            <a href="{{ route('reports.export', ['type' => 'aging', 'from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}" class="btn-secondary !px-3 !py-2 text-xs"><x-icon name="download" class="h-4 w-4" /> {{ __('reports.export_aging') }}</a>
        </div>
        <div class="grid grid-cols-1 divide-y divide-slate-100 sm:grid-cols-2 sm:divide-y-0 sm:divide-x sm:divide-slate-100 lg:grid-cols-4 rtl:sm:divide-x-reverse [&>div]:border-slate-100 rtl:[&>div]:border-l">
            @foreach($aging as $bucket)
                <div class="p-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $bucket['label'] }} {{ __('fields.days') }}</p>
                    <p class="mt-2 text-2xl font-bold {{ $bucket['total'] > 0 ? 'text-slate-900' : 'text-slate-300' }}">{{ money($bucket['total']) }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ __('reports.aging_counts', ['count' => $bucket['count']]) }}</p>
                </div>
            @endforeach
        </div>
        @if(collect($aging)->sum('count') > 0)
            <div class="table-wrap border-t border-slate-100">
                <table class="data-table">
                    <thead><tr><th>{{ __('fields.invoice') }}</th><th>{{ __('fields.customer') }}</th><th>{{ __('fields.due_date') }}</th><th class="text-end">{{ __('reports.days_overdue') }}</th><th class="text-end">{{ __('fields.balance') }}</th></tr></thead>
                    <tbody>
                    @foreach($aging as $bucket)
                        @foreach($bucket['invoices'] as $entry)
                            <tr>
                                <td><a href="{{ route('invoices.show', $entry['invoice']) }}" class="font-mono text-xs font-bold text-brand-800 hover:underline">{{ $entry['invoice']->number }}</a></td>
                                <td>{{ $entry['invoice']->customer?->name }}</td>
                                <td class="text-slate-500">{{ $entry['invoice']->due_date?->format('Y-m-d') ?? '—' }}</td>
                                <td class="text-end text-slate-500">{{ $entry['days'] }}</td>
                                <td class="text-end font-semibold text-rose-600">{{ money($entry['balance']) }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
        <section class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div><h2 class="section-title">{{ __('reports.sales_by_customer') }}</h2><p class="muted mt-0.5">{{ __('reports.from_to', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}</p></div>
                <a href="{{ route('reports.export', ['type' => 'customers', 'from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}" class="icon-btn" title="{{ __('reports.export_customers') }}"><x-icon name="download" class="h-4 w-4" /></a>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>{{ __('fields.customer') }}</th><th class="text-end">{{ __('reports.invoices') }}</th><th class="text-end">{{ __('fields.total') }}</th></tr></thead>
                    <tbody>
                    @forelse($salesByCustomer as $row)
                        <tr><td class="font-medium text-slate-700">{{ $row->customer?->name ?? '—' }}</td><td class="text-end text-slate-500">{{ $row->invoices }}</td><td class="text-end font-bold">{{ money((int) $row->total) }}</td></tr>
                    @empty
                        <tr><td colspan="3"><x-empty-state icon="customers" :title="__('reports.no_data')" :description="__('reports.date_range')" /></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div><h2 class="section-title">{{ __('reports.sales_by_product') }}</h2><p class="muted mt-0.5">{{ __('reports.from_to', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}</p></div>
                <a href="{{ route('reports.export', ['type' => 'products', 'from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}" class="icon-btn" title="{{ __('reports.export_products') }}"><x-icon name="download" class="h-4 w-4" /></a>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>{{ __('fields.product') }}</th><th class="text-end">{{ __('reports.qty') }}</th><th class="text-end">{{ __('fields.total') }}</th></tr></thead>
                    <tbody>
                    @forelse($salesByProduct as $row)
                        <tr><td class="max-w-xs truncate">{{ $row->name }}</td><td class="text-end text-slate-500">{{ quantity($row->quantity) }}</td><td class="text-end font-bold">{{ money((int) $row->total) }}</td></tr>
                    @empty
                        <tr><td colspan="3"><x-empty-state icon="products" :title="__('reports.no_data')" :description="__('reports.date_range')" /></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <section class="card mt-6 overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
            <div><h2 class="section-title">{{ __('reports.stock_valuation') }}</h2><p class="muted mt-0.5">{{ __('reports.stock_note') }}</p></div>
            <div class="flex items-center gap-3">
                <span class="badge bg-brand-50 text-brand-800 ring-brand-600/10">{{ __('reports.stock_total') }}: {{ money($stockTotal) }}</span>
                <a href="{{ route('reports.export', ['type' => 'stock', 'from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}" class="btn-secondary !px-3 !py-2 text-xs"><x-icon name="download" class="h-4 w-4" /> {{ __('reports.export_stock') }}</a>
            </div>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>{{ __('fields.product') }}</th><th>{{ __('fields.sku') }}</th><th>{{ __('fields.category') }}</th><th class="text-end">{{ __('fields.quantity') }}</th><th class="text-end">{{ __('fields.cost') }}</th><th class="text-end">{{ __('reports.value') }}</th></tr></thead>
                <tbody>
                @forelse($stock as $row)
                    <tr>
                        <td><a href="{{ route('products.show', $row['product']) }}" class="font-semibold text-slate-800 hover:text-brand-800">{{ $row['product']->displayName() }}</a></td>
                        <td class="font-mono text-xs text-slate-500">{{ $row['product']->sku }}</td>
                        <td class="text-slate-500">{{ $row['product']->category?->displayName() ?? '—' }}</td>
                        <td class="text-end">{{ quantity($row['quantity']) }}</td>
                        <td class="text-end text-slate-500">{{ money($row['unit_cost']) }}</td>
                        <td class="text-end font-bold">{{ money($row['value']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-empty-state icon="inventory" :title="__('reports.no_data')" :description="__('reports.stock_note')" /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
