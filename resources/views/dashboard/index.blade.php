{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')

@section('title', __('dashboard.title'))

@section('content')
    @php
        $overdueBalance = $overdue->sum(fn ($invoice) => max(0, $invoice->total_minor - (int) ($invoice->paid_total ?? 0)));
        $maxMonthly = max(1, $monthlySales->max('total'));
        $statusTotal = max(1, $statusCounts->sum());
        $statusLabels = [
            'draft' => ['label' => __('statuses.draft'), 'dot' => 'bg-brand-300', 'text' => 'text-brand-300'],
            'issued' => ['label' => __('statuses.issued'), 'dot' => 'bg-brand-500', 'text' => 'text-brand-500'],
            'partially_paid' => ['label' => __('statuses.partially_paid'), 'dot' => 'bg-accent-500', 'text' => 'text-accent-500'],
            'paid' => ['label' => __('statuses.paid'), 'dot' => 'bg-positive-600', 'text' => 'text-positive-600'],
            'cancelled' => ['label' => __('statuses.cancelled'), 'dot' => 'bg-negative-600', 'text' => 'text-negative-600'],
        ];
    @endphp

    <x-page-header :title="__('dashboard.greeting', ['name' => auth()->user()->name])" :subtitle="__('dashboard.subtitle', ['company' => company()->displayName()])">
        <x-slot:actions>
            @can('invoices.manage')
                <a href="{{ route('invoices.create') }}" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> {{ __('dashboard.new_invoice') }}</a>
            @endcan
            @can('quotations.manage')
                <a href="{{ route('quotations.create') }}" class="btn-secondary"><x-icon name="quotations" class="h-4 w-4" /> {{ __('dashboard.new_quotation') }}</a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-6">
        <x-stat-card :label="__('dashboard.sales_this_month')" :amount-minor="$salesThisMonth" icon="reports" tone="brand" :hint="__('dashboard.sales_hint')" class="xl:col-span-2" />
        <x-stat-card :label="__('dashboard.outstanding')" :amount-minor="$outstanding" icon="invoices" tone="amber" :hint="__('dashboard.outstanding_hint', ['count' => $outstandingInvoices->count()])" :href="auth()->user()->can('invoices.view') ? route('invoices.index', ['status' => 'issued']) : null" class="xl:col-span-2" />
        <x-stat-card :label="__('dashboard.overdue')" :value="$overdue->count()" icon="alert" tone="rose" :hint="__('dashboard.overdue_hint', ['count' => $overdue->count(), 'amount' => money($overdueBalance)])" :href="auth()->user()->can('invoices.view') ? route('invoices.index', ['overdue' => 1]) : null" class="xl:col-span-2" />
        <x-stat-card :label="__('dashboard.expenses_this_month')" :amount-minor="$expensesThisMonth" icon="expenses" tone="slate" :hint="__('dashboard.expenses_hint')" class="xl:col-span-3" />
        <x-stat-card :label="__('dashboard.low_stock')" :value="$lowStockCount" icon="alert" tone="accent" :hint="__('dashboard.low_stock_hint')" :href="auth()->user()->can('inventory.view') ? route('inventory.low-stock') : null" class="xl:col-span-3" />
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        <section class="card p-5 sm:p-6 xl:col-span-2">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="section-title">{{ __('dashboard.monthly_sales') }}</h2>
                    <p class="muted mt-1">{{ __('dashboard.monthly_sales_subtitle') }}</p>
                </div>
                <span class="badge bg-brand-50 text-brand-800 ring-brand-600/10">{{ __('dashboard.sales_total') }} {{ money($monthlySales->sum('total')) }}</span>
            </div>
            @php
                $months = $monthlySales->values();
                $monthCount = max(1, $months->count());
                $barWidth = 52;
                $barGap = 18;
                $chartWidth = $monthCount * $barWidth + ($monthCount - 1) * $barGap;
                $chartHeight = 170;
                $chartTotalHeight = $chartHeight + 22;
            @endphp
            <div class="mt-6" dir="ltr">
                <svg viewBox="0 0 {{ $chartWidth }} {{ $chartTotalHeight }}" class="h-56 w-full" role="img" aria-label="{{ __('dashboard.monthly_sales') }}" preserveAspectRatio="xMidYMid meet">
                    <defs>
                        <linearGradient id="bf-sales-bar" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0" stop-color="#B08D57"/>
                            <stop offset="1" stop-color="#14213D"/>
                        </linearGradient>
                    </defs>
                    @foreach($months as $i => $month)
                        @php($barHeight = $month['total'] > 0 ? max(8, (int) round(($month['total'] / $maxMonthly) * ($chartHeight - 8))) : 3)
                        @php($x = $i * ($barWidth + $barGap))
                        <rect x="{{ $x }}" y="{{ $chartHeight - $barHeight }}" width="{{ $barWidth }}" height="{{ $barHeight }}" rx="4" fill="url(#bf-sales-bar)">
                            <title>{{ $month['label'] }} — {{ money($month['total']) }}</title>
                        </rect>
                        <text x="{{ $x + $barWidth / 2 }}" y="{{ $chartHeight + 15 }}" text-anchor="middle" font-size="11" fill="#42577d">{{ $month['label'] }}</text>
                    @endforeach
                </svg>
                <p class="mt-1 text-end text-[10px] font-semibold text-slate-400">{{ __('dashboard.sales_total') }} {{ money($monthlySales->sum('total')) }}</p>
            </div>
        </section>

        <section class="card p-5 sm:p-6">
            <h2 class="section-title">{{ __('dashboard.invoice_status') }}</h2>
            <div class="mt-5 space-y-4">
                @foreach($statusLabels as $status => $meta)
                    @php($count = (int) ($statusCounts[$status] ?? 0))
                    <div>
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="flex items-center gap-2 font-medium text-slate-600"><span class="h-2.5 w-2.5 rounded-full {{ $meta['dot'] }}"></span>{{ $meta['label'] }}</span>
                            <span class="font-bold text-slate-800">{{ $count }}</span>
                        </div>
                        @php($percent = round(($count / $statusTotal) * 100))
                        <svg viewBox="0 0 100 4" preserveAspectRatio="none" class="mt-1.5 h-1.5 w-full {{ $meta['text'] }}" role="img" aria-label="{{ $meta['label'] }}: {{ $count }}">
                            <rect width="100" height="4" rx="2" fill="#E4DFD5"/>
                            <rect width="{{ $percent }}" height="4" rx="2" fill="currentColor"/>
                        </svg>
                    </div>
                @endforeach
            </div>
            @can('invoices.view')
                <a href="{{ route('invoices.index') }}" class="btn-secondary mt-6 w-full">{{ __('common.view_all') }}</a>
            @endcan
        </section>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        <section class="card overflow-hidden xl:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <h2 class="section-title">{{ __('dashboard.recent_activity') }}</h2>
                    <p class="muted mt-0.5">{{ __('dashboard.recent_activity_subtitle') }}</p>
                </div>
                @can('activity.view')
                    <a href="{{ route('activity.index') }}" class="text-xs font-bold text-brand-800 hover:underline">{{ __('common.view_all') }}</a>
                @endcan
            </div>
            @if($recentActivity->isEmpty())
                <x-empty-state icon="activity" :title="__('dashboard.no_activity')" :description="__('common.no_results_hint')" />
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach($recentActivity as $log)
                        <li class="flex items-start gap-3 px-5 py-3.5">
                            <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-50 text-xs font-bold text-brand-800">{{ $log->user ? mb_substr($log->user->name, 0, 1) : '•' }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-slate-700">{{ __('activity.actions.'.$log->action) }}</p>
                                <p class="mt-0.5 text-[11px] text-slate-400">{{ $log->user?->name ?? __('common.system') }} · {{ $log->created_at->diffForHumans() }}</p>
                            </div>
                            <span class="badge bg-slate-100 text-slate-500 ring-slate-500/10">{{ __('activity.modules.'.$log->module) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="card overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="section-title">{{ __('dashboard.top_customers') }}</h2>
                <p class="muted mt-0.5">{{ __('dashboard.top_customers_subtitle') }}</p>
            </div>
            @if($topCustomers->isEmpty())
                <x-empty-state icon="customers" :title="__('dashboard.no_sales')" :description="__('common.no_results_hint')" />
            @else
                <div class="divide-y divide-slate-100">
                    @foreach($topCustomers as $index => $row)
                        <div class="flex items-center gap-3 px-5 py-3.5">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-xs font-bold text-slate-600">{{ $index + 1 }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-slate-700">{{ $row->customer?->name ?? __('common.not_available') }}</p>
                                @if($row->customer?->company_name)<p class="truncate text-[11px] text-slate-400">{{ $row->customer->company_name }}</p>@endif
                            </div>
                            <span class="text-xs font-bold text-slate-800">{{ money((int) $row->total) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        <section class="card overflow-hidden xl:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <h2 class="section-title">{{ __('dashboard.low_stock') }}</h2>
                    <p class="muted mt-0.5">{{ __('dashboard.low_stock_hint') }}</p>
                </div>
                @can('inventory.view')
                    <a href="{{ route('inventory.low-stock') }}" class="text-xs font-bold text-brand-800 hover:underline">{{ __('common.view_all') }}</a>
                @endcan
            </div>
            @if($lowStock->isEmpty())
                <div class="px-5 py-8 text-center text-sm text-slate-500">
                    <x-icon name="check" class="mx-auto mb-2 h-8 w-8 text-emerald-500" />
                    {{ __('dashboard.no_low_stock') }}
                </div>
            @else
                <div class="table-wrap">
                    <table class="data-table">
                        <thead><tr><th>{{ __('fields.product') }}</th><th>{{ __('fields.on_hand') }}</th><th>{{ __('fields.threshold') }}</th><th></th></tr></thead>
                        <tbody>
                        @foreach($lowStock as $product)
                            <tr>
                                <td><p class="font-semibold text-slate-800">{{ $product->displayName() }}</p><p class="text-[11px] text-slate-400">{{ $product->sku }}</p></td>
                                <td><span class="badge bg-amber-50 text-amber-700 ring-amber-600/20">{{ quantity($product->on_hand_quantity ?? 0) }}</span></td>
                                <td class="text-slate-500">{{ quantity($product->low_stock_threshold) }}</td>
                                <td class="text-end"><a href="{{ route('products.show', $product) }}" class="text-xs font-bold text-brand-800 hover:underline">{{ __('common.view') }}</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="card p-5 sm:p-6">
            <h2 class="section-title">{{ __('dashboard.quick_actions') }}</h2>
            <div class="mt-4 grid grid-cols-2 gap-3">
                @can('invoices.manage')
                    <a href="{{ route('invoices.create') }}" class="rounded-xl border border-slate-200 p-4 transition hover:border-brand-300 hover:bg-brand-50/50"><x-icon name="invoices" class="h-5 w-5 text-brand-800" /><p class="mt-2 text-xs font-bold text-slate-700">{{ __('dashboard.new_invoice') }}</p></a>
                @endcan
                @can('customers.manage')
                    <a href="{{ route('customers.create') }}" class="rounded-xl border border-slate-200 p-4 transition hover:border-brand-300 hover:bg-brand-50/50"><x-icon name="customers" class="h-5 w-5 text-brand-800" /><p class="mt-2 text-xs font-bold text-slate-700">{{ __('dashboard.new_customer') }}</p></a>
                @endcan
                @can('expenses.manage')
                    <a href="{{ route('expenses.create') }}" class="rounded-xl border border-slate-200 p-4 transition hover:border-brand-300 hover:bg-brand-50/50"><x-icon name="expenses" class="h-5 w-5 text-brand-800" /><p class="mt-2 text-xs font-bold text-slate-700">{{ __('dashboard.new_expense') }}</p></a>
                @endcan
                @can('payments.manage')
                    <a href="{{ route('payments.create') }}" class="rounded-xl border border-slate-200 p-4 transition hover:border-brand-300 hover:bg-brand-50/50"><x-icon name="payments" class="h-5 w-5 text-brand-800" /><p class="mt-2 text-xs font-bold text-slate-700">{{ __('dashboard.record_payment') }}</p></a>
                @endcan
            </div>
            @if($overdue->isNotEmpty())
                <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4">
                    <p class="flex items-center gap-2 text-xs font-bold text-amber-800"><x-icon name="alert" class="h-4 w-4" /> {{ __('dashboard.overdue') }}</p>
                    <p class="mt-1 text-xs leading-5 text-amber-700">{{ __('dashboard.overdue_hint', ['count' => $overdue->count(), 'amount' => money($overdueBalance)]) }}</p>
                </div>
            @endif
        </section>
    </div>
@endsection
