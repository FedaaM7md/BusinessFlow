{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', __('inventory.low_stock_title'))
@section('content')
    <x-page-header :title="__('inventory.low_stock_title')" :subtitle="__('inventory.low_stock_subtitle')">
        <x-slot:actions>
            <a href="{{ route('inventory.index') }}" class="btn-secondary">{{ __('inventory.stock_ledger') }}</a>
            @can('inventory.manage')<a href="{{ route('inventory.create') }}" class="btn-primary">{{ __('inventory.create') }}</a>@endcan
        </x-slot:actions>
    </x-page-header>

    <div class="card overflow-hidden">
        <div class="divide-y divide-grid md:hidden">
            @forelse($products as $product)
                @php($onHand = (float) ($product->on_hand_quantity ?? 0))
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <a href="{{ route('products.show', $product) }}" class="break-words text-sm font-semibold text-brand-900">{{ $product->displayName() }}</a>
                            <p class="code mt-0.5 text-[11px] text-brand-400">{{ $product->sku }}</p>
                        </div>
                        <span class="badge {{ $onHand <= 0 ? 'bg-negative-50 text-negative-700 ring-negative-600/20' : 'bg-accent-50 text-accent-700 ring-accent-500/20' }}">{{ $onHand <= 0 ? __('inventory.out_of_stock') : __('statuses.low') }}</span>
                    </div>
                    <dl class="mt-2 grid grid-cols-2 gap-2 text-xs text-brand-500">
                        <div><dt class="text-[10px] text-brand-400">{{ __('fields.on_hand') }}</dt><dd class="amount font-bold {{ $onHand <= 0 ? 'text-negative-600' : 'text-accent-700' }}">{{ quantity($onHand) }} <span class="text-[11px] font-normal text-brand-400">{{ $product->unit }}</span></dd></div>
                        <div class="text-end"><dt class="text-[10px] text-brand-400">{{ __('fields.threshold') }}</dt><dd class="amount text-brand-600">{{ quantity($product->low_stock_threshold) }}</dd></div>
                    </dl>
                    @can('inventory.manage')
                        <div class="mt-3 flex justify-end">
                            <a href="{{ route('inventory.create', ['product' => $product->id]) }}" class="btn-secondary !px-3 !py-1.5 text-xs">{{ __('products.restock') }}</a>
                        </div>
                    @endcan
                </div>
            @empty
                <x-empty-state icon="check" :title="__('inventory.all_clear')" :description="__('dashboard.no_low_stock')" />
            @endforelse
        </div>
        <div class="hidden md:block">
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>{{ __('fields.product') }}</th><th>{{ __('fields.category') }}</th><th class="text-end">{{ __('fields.on_hand') }}</th><th class="text-end">{{ __('fields.threshold') }}</th><th>{{ __('common.status') }}</th><th class="text-end">{{ __('common.actions') }}</th></tr></thead>
                    <tbody>
                    @forelse($products as $product)
                        @php($onHand = (float) ($product->on_hand_quantity ?? 0))
                        <tr>
                            <td><a href="{{ route('products.show', $product) }}" class="font-semibold text-slate-800 hover:text-brand-800">{{ $product->displayName() }}</a><p class="text-[11px] text-slate-400">{{ $product->sku }}</p></td>
                            <td class="text-slate-500">{{ $product->category?->displayName() ?? '—' }}</td>
                            <td class="text-end font-bold {{ $onHand <= 0 ? 'text-rose-600' : 'text-amber-700' }}">{{ quantity($onHand) }} <span class="text-[11px] font-normal text-slate-400">{{ $product->unit }}</span></td>
                            <td class="text-end text-slate-500">{{ quantity($product->low_stock_threshold) }}</td>
                            <td><span class="badge {{ $onHand <= 0 ? 'bg-rose-50 text-rose-700 ring-rose-600/20' : 'bg-amber-50 text-amber-700 ring-amber-600/20' }}">{{ $onHand <= 0 ? __('inventory.out_of_stock') : __('statuses.low') }}</span></td>
                            <td class="text-end">
                                @can('inventory.manage')<a href="{{ route('inventory.create', ['product' => $product->id]) }}" class="btn-secondary !px-3 !py-1.5 text-xs">{{ __('products.restock') }}</a>@endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-empty-state icon="check" :title="__('inventory.all_clear')" :description="__('dashboard.no_low_stock')" /></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <x-pagination :paginator="$products" />
    </div>
@endsection
