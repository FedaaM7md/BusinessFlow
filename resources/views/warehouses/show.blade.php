{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', $warehouse->name)
@section('content')
    <x-page-header :title="$warehouse->name" :subtitle="$warehouse->code . ($warehouse->location ? ' · '.$warehouse->location : '')">
        <x-slot:actions>
            @can('inventory.manage')<a href="{{ route('warehouses.edit', $warehouse) }}" class="btn-secondary"><x-icon name="edit" class="h-4 w-4" /> {{ __('common.edit') }}</a>@endcan
            @can('inventory.manage')<a href="{{ route('inventory.create') }}" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> {{ __('inventory.create') }}</a>@endcan
            <a href="{{ route('warehouses.index') }}" class="btn-ghost">{{ __('common.back') }}</a>
        </x-slot:actions>
    </x-page-header>

    <div class="card overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <div><h2 class="section-title">{{ __('inventory.warehouse_stock') }}</h2><p class="muted mt-0.5">{{ __('inventory.warehouses_subtitle') }}</p></div>
            @if($warehouse->is_default)<span class="badge bg-brand-50 text-brand-800 ring-brand-600/10">{{ __('inventory.default') }}</span>@endif
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>{{ __('fields.product') }}</th><th>{{ __('fields.sku') }}</th><th>{{ __('fields.category') }}</th><th class="text-end">{{ __('fields.on_hand') }}</th><th class="text-end">{{ __('fields.cost') }}</th><th class="text-end">{{ __('reports.value') }}</th></tr></thead>
                <tbody>
                @forelse($products as $product)
                    @php($qty = (float) ($product->warehouse_quantity ?? 0))
                    <tr>
                        <td><a href="{{ route('products.show', $product) }}" class="font-semibold text-slate-800 hover:text-brand-800">{{ $product->displayName() }}</a></td>
                        <td class="font-mono text-xs text-slate-500">{{ $product->sku }}</td>
                        <td class="text-slate-500">{{ $product->category?->displayName() ?? '—' }}</td>
                        <td class="text-end font-bold {{ $qty <= (float) $product->low_stock_threshold ? 'text-amber-700' : 'text-slate-800' }}">{{ quantity($qty) }} <span class="text-[11px] font-normal text-slate-400">{{ $product->unit }}</span></td>
                        <td class="text-end text-slate-500">{{ money($product->cost_minor) }}</td>
                        <td class="text-end font-semibold text-slate-800">{{ money((int) round($qty * $product->cost_minor)) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-empty-state icon="inventory" :title="__('common.no_results')" :description="__('inventory.warehouses_subtitle')" /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination :paginator="$products" />
    </div>
@endsection
