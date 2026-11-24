{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', __('products.title'))
@section('content')
    <x-page-header :title="__('products.title')" :subtitle="__('products.subtitle')">
        <x-slot:actions>
            @can('products.manage')
                <a href="{{ route('categories.index') }}" class="btn-secondary">{{ __('products.categories_title') }}</a>
                <a href="{{ route('products.create') }}" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> {{ __('products.create') }}</a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-filter-panel>
        <form method="GET" action="{{ route('products.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-[1fr_160px_190px_150px_auto]">
            <div class="relative"><span class="pointer-events-none absolute inset-y-0 start-3 flex items-center text-slate-400"><x-icon name="search" class="h-4 w-4" /></span><input type="search" name="q" value="{{ request('q') }}" class="input ps-10" placeholder="{{ __('products.search_placeholder') }}"></div>
            <select name="type" class="select"><option value="">{{ __('products.all_types') }}</option><option value="product" @selected(request('type') === 'product')>{{ __('products.product') }}</option><option value="service" @selected(request('type') === 'service')>{{ __('products.service') }}</option></select>
            <select name="category" class="select"><option value="">{{ __('products.all_categories') }}</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->displayName() }}</option>@endforeach</select>
            <select name="status" class="select"><option value="">{{ __('common.all') }}</option><option value="active" @selected(request('status') === 'active')>{{ __('common.active') }}</option><option value="inactive" @selected(request('status') === 'inactive')>{{ __('common.inactive') }}</option></select>
            <div class="flex gap-2"><button class="btn-primary">{{ __('common.apply') }}</button><a href="{{ route('products.index') }}" class="btn-ghost">{{ __('common.reset') }}</a></div>
        </form>
    </x-filter-panel>

    <div class="card overflow-hidden">
        <div class="divide-y divide-grid md:hidden">
            @forelse($products as $product)
                @php($onHand = (float) ($product->on_hand_quantity ?? 0))
                @php($isLow = $product->is_tracked && $onHand <= (float) $product->low_stock_threshold)
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <a href="{{ route('products.show', $product) }}" class="break-words text-sm font-semibold text-brand-900">{{ $product->displayName() }}</a>
                        <span class="badge {{ $product->is_active ? 'bg-positive-50 text-positive-700 ring-positive-600/20' : 'bg-brand-50 text-brand-500 ring-brand-500/10' }}">{{ $product->is_active ? __('common.active') : __('common.inactive') }}</span>
                    </div>
                    <p class="code mt-0.5 text-[11px] text-brand-400">{{ $product->sku }}</p>
                    <dl class="mt-2 grid grid-cols-2 gap-2 text-xs text-brand-500">
                        <div><dt class="text-[10px] text-brand-400">{{ __('fields.price') }}</dt><dd class="amount font-bold text-brand-900">{{ money($product->price_minor) }}</dd></div>
                        <div class="text-end"><dt class="text-[10px] text-brand-400">{{ __('products.stock') }}</dt><dd>@if($product->is_tracked)<span class="badge {{ $isLow ? 'bg-accent-50 text-accent-700 ring-accent-500/20' : 'bg-positive-50 text-positive-700 ring-positive-600/20' }}">{{ quantity($onHand) }}</span>@else<span class="text-[11px] text-brand-400">{{ __('products.not_tracked') }}</span>@endif</dd></div>
                    </dl>
                    <div class="mt-3 flex items-center justify-end gap-1">
                        <a href="{{ route('products.show', $product) }}" class="icon-btn" title="{{ __('common.view') }}"><x-icon name="eye" class="h-4 w-4" /></a>
                        @can('products.manage')<a href="{{ route('products.edit', $product) }}" class="icon-btn" title="{{ __('common.edit') }}"><x-icon name="edit" class="h-4 w-4" /></a>@endcan
                    </div>
                </div>
            @empty
                <x-empty-state icon="products" :title="__('common.no_results')" :description="__('products.subtitle')" />
            @endforelse
        </div>
        <div class="hidden md:block">
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr>
                    <x-sortable-th column="sku" :sort="$sort" :direction="$direction">{{ __('fields.sku') }}</x-sortable-th>
                    <x-sortable-th column="name" :sort="$sort" :direction="$direction">{{ __('fields.name') }}</x-sortable-th>
                    <th>{{ __('fields.category') }}</th>
                    <th>{{ __('fields.type') }}</th>
                    <x-sortable-th column="price_minor" :sort="$sort" :direction="$direction">{{ __('fields.price') }}</x-sortable-th>
                    <th>{{ __('fields.tax_rate') }}</th>
                    <th>{{ __('products.stock') }}</th>
                    <th>{{ __('common.status') }}</th>
                    <th class="text-end">{{ __('common.actions') }}</th>
                </tr></thead>
                <tbody>
                @forelse($products as $product)
                    @php($onHand = (float) ($product->on_hand_quantity ?? 0))
                    @php($isLow = $product->is_tracked && $onHand <= (float) $product->low_stock_threshold)
                    <tr>
                        <td><span class="font-mono text-xs text-slate-500">{{ $product->sku }}</span></td>
                        <td><a href="{{ route('products.show', $product) }}" class="font-semibold text-slate-800 hover:text-brand-800">{{ $product->displayName() }}</a>@if(app()->getLocale() === 'en' && $product->name_ar)<p class="text-[11px] text-slate-400">{{ $product->name_ar }}</p>@endif</td>
                        <td class="text-slate-500">{{ $product->category?->displayName() ?? '—' }}</td>
                        <td><span class="badge {{ $product->type === 'service' ? 'bg-violet-50 text-violet-700 ring-violet-600/20' : 'bg-slate-100 text-slate-600 ring-slate-500/10' }}">{{ __('products.'.$product->type) }}</span></td>
                        <td class="whitespace-nowrap font-semibold text-slate-800">{{ money($product->price_minor) }}</td>
                        <td class="text-slate-500">{{ \App\Modules\Core\Services\Money::basisPointsToPercent($product->tax_rate) }}%</td>
                        <td>
                            @if($product->is_tracked)
                                <span class="badge {{ $isLow ? 'bg-amber-50 text-amber-700 ring-amber-600/20' : 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' }}">{{ quantity($onHand) }}</span>
                                <span class="ms-1 text-[11px] text-slate-400">{{ $product->unit }}</span>
                            @else
                                <span class="text-xs text-slate-400">{{ __('products.not_tracked') }}</span>
                            @endif
                        </td>
                        <td><span class="badge {{ $product->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-slate-100 text-slate-500 ring-slate-500/10' }}">{{ $product->is_active ? __('common.active') : __('common.inactive') }}</span></td>
                        <td><div class="flex justify-end gap-1">
                            <a href="{{ route('products.show', $product) }}" class="icon-btn" title="{{ __('common.view') }}"><x-icon name="eye" class="h-4 w-4" /></a>
                            @can('products.manage')<a href="{{ route('products.edit', $product) }}" class="icon-btn" title="{{ __('common.edit') }}"><x-icon name="edit" class="h-4 w-4" /></a>@endcan
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="9"><x-empty-state icon="products" :title="__('common.no_results')" :description="__('products.subtitle')" /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        </div>
        <x-pagination :paginator="$products" />
    </div>
@endsection
