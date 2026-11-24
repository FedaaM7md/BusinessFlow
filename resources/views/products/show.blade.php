{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', $product->displayName())
@section('content')
    @php($onHand = $product->onHand())
    <x-page-header :title="$product->displayName()" :subtitle="$product->sku . ' · '.__('products.'.$product->type)">
        <x-slot:actions>
            @can('inventory.manage')@if($product->is_tracked)<a href="{{ route('inventory.create', ['product' => $product->id]) }}" class="btn-primary"><x-icon name="movements" class="h-4 w-4" /> {{ __('products.restock') }}</a>@endif @endcan
            @can('products.manage')<a href="{{ route('products.edit', $product) }}" class="btn-secondary"><x-icon name="edit" class="h-4 w-4" /> {{ __('common.edit') }}</a>@endcan
            <a href="{{ route('products.index') }}" class="btn-ghost">{{ __('common.back') }}</a>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="card p-5"><p class="kpi-label">{{ __('fields.price') }}</p><p class="mt-1 text-xl font-bold text-slate-900">{{ money($product->price_minor) }}</p></div>
                <div class="card p-5"><p class="kpi-label">{{ __('fields.cost') }}</p><p class="mt-1 text-xl font-bold text-slate-900">{{ money($product->cost_minor) }}</p></div>
                <div class="card p-5"><p class="kpi-label">{{ __('products.margin') }}</p><p class="mt-1 text-xl font-bold text-emerald-700">{{ money($product->price_minor - $product->cost_minor) }}</p></div>
                <div class="card p-5"><p class="kpi-label">{{ __('products.stock') }}</p><p class="mt-1 text-xl font-bold {{ $product->isLowStock() ? 'text-amber-700' : 'text-slate-900' }}">{{ $product->is_tracked ? quantity($onHand) : '—' }}</p><p class="text-[11px] text-slate-400">{{ $product->unit }}</p></div>
            </div>

            <section class="card overflow-hidden">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div><h2 class="section-title">{{ __('products.stock_history') }}</h2><p class="muted mt-0.5">{{ __('inventory.stock_ledger') }}</p></div>
                    @if($product->is_tracked)<span class="badge {{ $product->isLowStock() ? 'bg-amber-50 text-amber-700 ring-amber-600/20' : 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' }}">{{ __('fields.low_stock_threshold') }}: {{ quantity($product->low_stock_threshold) }}</span>@endif
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead><tr><th>{{ __('fields.moved_at') }}</th><th>{{ __('fields.type') }}</th><th>{{ __('fields.warehouse') }}</th><th class="text-end">{{ __('fields.quantity') }}</th><th>{{ __('fields.reference') }}</th><th>{{ __('activity.user') }}</th></tr></thead>
                        <tbody>
                        @forelse($movements as $movement)
                            <tr>
                                <td class="whitespace-nowrap text-slate-500">{{ $movement->moved_at->format('Y-m-d') }}</td>
                                <td><x-status-badge :status="$movement->type" /></td>
                                <td class="text-slate-500">{{ $movement->warehouse?->name }}</td>
                                <td class="text-end font-bold {{ $movement->quantity < 0 ? 'text-rose-600' : 'text-emerald-700' }}">{{ $movement->quantity > 0 ? '+' : '' }}{{ quantity($movement->quantity) }}</td>
                                <td class="max-w-xs truncate text-xs text-slate-500">{{ $movement->note ?: $movement->reference_type }}</td>
                                <td class="text-xs text-slate-500">{{ $movement->creator?->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-empty-state icon="movements" :title="__('products.no_stock_history')" :description="__('inventory.subtitle')" /></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <x-pagination :paginator="$movements" />
            </section>
        </div>

        <aside class="space-y-6">
            <section class="card p-5">
                <h2 class="section-title">{{ __('products.profile') }}</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-3"><dt class="text-slate-400">{{ __('fields.sku') }}</dt><dd class="font-mono font-medium text-slate-700">{{ $product->sku }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-400">{{ __('fields.category') }}</dt><dd class="font-medium text-slate-700">{{ $product->category?->displayName() ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-400">{{ __('fields.tax_rate') }}</dt><dd class="font-medium text-slate-700">{{ \App\Modules\Core\Services\Money::basisPointsToPercent($product->tax_rate) }}%</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-400">{{ __('fields.tracked') }}</dt><dd>{{ $product->is_tracked ? __('common.yes') : __('common.no') }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-400">{{ __('common.status') }}</dt><dd><span class="badge {{ $product->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-slate-100 text-slate-500 ring-slate-500/10' }}">{{ $product->is_active ? __('common.active') : __('common.inactive') }}</span></dd></div>
                </dl>
                @if($product->notes)<p class="mt-4 border-t border-slate-100 pt-4 text-xs leading-6 text-slate-500">{{ $product->notes }}</p>@endif
            </section>

            @if($product->is_tracked)
                <section class="card p-5">
                    <h2 class="section-title">{{ __('products.warehouse_breakdown') }}</h2>
                    <div class="mt-4 space-y-2">
                        @foreach($warehouses as $warehouse)
                            <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2 text-sm">
                                <span class="text-slate-600">{{ $warehouse->name }}</span>
                                <span class="font-bold text-slate-800">{{ quantity($warehouseStock[$warehouse->id] ?? 0) }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </aside>
    </div>
@endsection
