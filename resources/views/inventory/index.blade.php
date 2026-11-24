{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', __('inventory.title'))
@section('content')
    <x-page-header :title="__('inventory.title')" :subtitle="__('inventory.subtitle')">
        <x-slot:actions>
            @can('inventory.view')<a href="{{ route('inventory.low-stock') }}" class="btn-secondary">{{ __('inventory.low_stock_title') }}</a>@endcan
            @can('inventory.manage')<a href="{{ route('inventory.create') }}" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> {{ __('inventory.create') }}</a>@endcan
        </x-slot:actions>
    </x-page-header>

    <x-filter-panel>
        <form method="GET" action="{{ route('inventory.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-[1fr_180px_170px_150px_150px_auto]">
            <select name="product" class="select"><option value="">{{ __('inventory.all_products') }}</option>@foreach($products as $product)<option value="{{ $product->id }}" @selected((string) request('product') === (string) $product->id)>{{ $product->displayName() }}</option>@endforeach</select>
            <select name="warehouse" class="select"><option value="">{{ __('inventory.all_warehouses') }}</option>@foreach($warehouses as $warehouse)<option value="{{ $warehouse->id }}" @selected((string) request('warehouse') === (string) $warehouse->id)>{{ $warehouse->name }}</option>@endforeach</select>
            <select name="type" class="select"><option value="">{{ __('inventory.all_types') }}</option>@foreach(['in','out','adjustment','transfer'] as $type)<option value="{{ $type }}" @selected(request('type') === $type)>{{ __('statuses.'.$type) }}</option>@endforeach</select>
            <x-date-input name="from" :value="request('from')" />
            <x-date-input name="to" :value="request('to')" />
            <div class="flex gap-2"><button class="btn-primary">{{ __('common.apply') }}</button><a href="{{ route('inventory.index') }}" class="btn-ghost">{{ __('common.reset') }}</a></div>
        </form>
    </x-filter-panel>

    <div class="card overflow-hidden">
        <div class="divide-y divide-grid md:hidden">
            @forelse($movements as $movement)
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            @if($movement->product)
                                <a href="{{ route('products.show', $movement->product) }}" class="break-words text-sm font-semibold text-brand-900">{{ $movement->product->displayName() }}</a>
                                <p class="code mt-0.5 text-[11px] text-brand-400">{{ $movement->product->sku }}</p>
                            @else
                                <span class="text-sm text-brand-500">—</span>
                            @endif
                        </div>
                        <x-status-badge :status="$movement->type" />
                    </div>
                    <dl class="mt-2 grid grid-cols-2 gap-2 text-xs text-brand-500">
                        <div><dt class="text-[10px] text-brand-400">{{ __('fields.moved_at') }}</dt><dd class="amount text-brand-700">{{ $movement->moved_at->format('Y-m-d') }}</dd></div>
                        <div class="text-end"><dt class="text-[10px] text-brand-400">{{ __('fields.quantity') }}</dt><dd class="amount font-bold {{ $movement->quantity < 0 ? 'text-negative-600' : 'text-positive-700' }}">{{ $movement->quantity > 0 ? '+' : '' }}{{ quantity($movement->quantity) }}</dd></div>
                    </dl>
                    <p class="mt-2 text-xs text-brand-500">{{ $movement->warehouse?->name }}</p>
                    @if($movement->note)<p class="mt-1 text-[11px] text-brand-400">{{ $movement->note }}</p>@endif
                </div>
            @empty
                <x-empty-state icon="movements" :title="__('common.no_results')" :description="__('inventory.subtitle')" />
            @endforelse
        </div>
        <div class="hidden md:block">
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>{{ __('fields.moved_at') }}</th><th>{{ __('fields.product') }}</th><th>{{ __('fields.warehouse') }}</th><th>{{ __('fields.type') }}</th><th class="text-end">{{ __('fields.quantity') }}</th><th>{{ __('fields.reference') }}</th><th>{{ __('activity.user') }}</th></tr></thead>
                <tbody>
                @forelse($movements as $movement)
                    <tr>
                        <td class="whitespace-nowrap text-slate-500">{{ $movement->moved_at->format('Y-m-d') }}</td>
                        <td>@if($movement->product)<a href="{{ route('products.show', $movement->product) }}" class="font-semibold text-slate-800 hover:text-brand-800">{{ $movement->product->displayName() }}</a><p class="text-[11px] text-slate-400">{{ $movement->product->sku }}</p>@else — @endif</td>
                        <td class="text-slate-500">{{ $movement->warehouse?->name }}</td>
                        <td><x-status-badge :status="$movement->type" /></td>
                        <td class="text-end font-bold {{ $movement->quantity < 0 ? 'text-rose-600' : 'text-emerald-700' }}">{{ $movement->quantity > 0 ? '+' : '' }}{{ quantity($movement->quantity) }}</td>
                        <td class="max-w-xs"><p class="truncate text-xs text-slate-500">{{ $movement->note ?: '—' }}</p></td>
                        <td class="text-xs text-slate-500">{{ $movement->creator?->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7"><x-empty-state icon="movements" :title="__('common.no_results')" :description="__('inventory.subtitle')" /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        </div>
        <x-pagination :paginator="$movements" />
    </div>
@endsection
