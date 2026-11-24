{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', __('inventory.warehouses_title'))
@section('content')
    <x-page-header :title="__('inventory.warehouses_title')" :subtitle="__('inventory.warehouses_subtitle')">
        <x-slot:actions>@can('inventory.manage')<a href="{{ route('warehouses.create') }}" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> {{ __('inventory.new_warehouse') }}</a>@endcan</x-slot:actions>
    </x-page-header>
    <div class="card overflow-hidden">
        <div class="divide-y divide-grid md:hidden">
            @forelse($warehouses as $warehouse)
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <a href="{{ route('warehouses.show', $warehouse) }}" class="break-words text-sm font-semibold text-brand-900">{{ $warehouse->name }}</a>
                            <p class="code mt-0.5 text-[11px] text-brand-400">{{ $warehouse->code }}</p>
                        </div>
                        <span class="badge {{ $warehouse->is_active ? 'bg-positive-50 text-positive-700 ring-positive-600/20' : 'bg-brand-50 text-brand-500 ring-brand-500/10' }}">{{ $warehouse->is_active ? __('common.active') : __('common.inactive') }}</span>
                    </div>
                    <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-brand-500">
                        <span>{{ $warehouse->location ?: '—' }}</span>
                        <span aria-hidden="true">·</span>
                        <span>{{ $warehouse->movements_count }} {{ __('fields.movements') }}</span>
                        @if($warehouse->is_default)<span class="badge bg-brand-50 text-brand-700 ring-brand-500/10">{{ __('inventory.default') }}</span>@endif
                    </div>
                    <div class="mt-3 flex items-center justify-end gap-1">
                        <a href="{{ route('warehouses.show', $warehouse) }}" class="icon-btn" title="{{ __('common.view') }}"><x-icon name="eye" class="h-4 w-4" /></a>
                        @can('inventory.manage')
                            <a href="{{ route('warehouses.edit', $warehouse) }}" class="icon-btn" title="{{ __('common.edit') }}"><x-icon name="edit" class="h-4 w-4" /></a>
                            <form method="POST" action="{{ route('warehouses.destroy', $warehouse) }}" data-confirm="{{ __('common.confirm_delete_text') }}">@csrf @method('DELETE')<button type="submit" class="icon-btn hover:!bg-rose-50 hover:!text-rose-600" title="{{ __('common.delete') }}"><x-icon name="trash" class="h-4 w-4" /></button></form>
                        @endcan
                    </div>
                </div>
            @empty
                <x-empty-state icon="inventory" :title="__('common.no_results')" :description="__('inventory.warehouses_subtitle')" />
            @endforelse
        </div>
        <div class="hidden md:block">
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>{{ __('inventory.warehouse_code') }}</th><th>{{ __('fields.name') }}</th><th>{{ __('inventory.warehouse_location') }}</th><th>{{ __('fields.movements') }}</th><th>{{ __('common.status') }}</th><th class="text-end">{{ __('common.actions') }}</th></tr></thead>
                    <tbody>
                    @forelse($warehouses as $warehouse)
                        <tr>
                            <td><span class="font-mono text-xs text-slate-500">{{ $warehouse->code }}</span></td>
                            <td><a href="{{ route('warehouses.show', $warehouse) }}" class="font-semibold text-slate-800 hover:text-brand-800">{{ $warehouse->name }}</a>@if($warehouse->is_default)<span class="badge ms-2 bg-brand-50 text-brand-800 ring-brand-600/10">{{ __('inventory.default') }}</span>@endif</td>
                            <td class="text-slate-500">{{ $warehouse->location ?: '—' }}</td>
                            <td class="text-slate-500">{{ $warehouse->movements_count }}</td>
                            <td><span class="badge {{ $warehouse->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-slate-100 text-slate-500 ring-slate-500/10' }}">{{ $warehouse->is_active ? __('common.active') : __('common.inactive') }}</span></td>
                            <td><div class="flex justify-end gap-1">
                                <a href="{{ route('warehouses.show', $warehouse) }}" class="icon-btn" title="{{ __('common.view') }}"><x-icon name="eye" class="h-4 w-4" /></a>
                                @can('inventory.manage')
                                    <a href="{{ route('warehouses.edit', $warehouse) }}" class="icon-btn" title="{{ __('common.edit') }}"><x-icon name="edit" class="h-4 w-4" /></a>
                                    <form method="POST" action="{{ route('warehouses.destroy', $warehouse) }}" data-confirm="{{ __('common.confirm_delete_text') }}">@csrf @method('DELETE')<button class="icon-btn hover:!bg-rose-50 hover:!text-rose-600" title="{{ __('common.delete') }}"><x-icon name="trash" class="h-4 w-4" /></button></form>
                                @endcan
                            </div></td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-empty-state icon="inventory" :title="__('common.no_results')" :description="__('inventory.warehouses_subtitle')" /></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <x-pagination :paginator="$warehouses" />
    </div>
@endsection
