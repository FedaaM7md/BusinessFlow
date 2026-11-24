{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')

@section('title', __('suppliers.title'))

@section('content')
    <x-page-header :title="__('suppliers.title')" :subtitle="__('suppliers.subtitle')">
        <x-slot:actions>
            @can('suppliers.manage')
                <a href="{{ route('suppliers.create') }}" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> {{ __('suppliers.create') }}</a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-filter-panel>
        <form method="GET" action="{{ route('suppliers.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_180px_auto]">
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 start-3 flex items-center text-slate-400"><x-icon name="search" class="h-4 w-4" /></span>
                <input type="search" name="q" value="{{ request('q') }}" class="input ps-10" placeholder="{{ __('suppliers.search_placeholder') }}">
            </div>
            <select name="status" class="select">
                <option value="">{{ __('common.all') }}</option>
                <option value="active" @selected(request('status') === 'active')>{{ __('common.active') }}</option>
                <option value="inactive" @selected(request('status') === 'inactive')>{{ __('common.inactive') }}</option>
            </select>
            <div class="flex items-center gap-2">
                <button type="submit" class="btn-primary">{{ __('common.apply') }}</button>
                <a href="{{ route('suppliers.index') }}" class="btn-ghost">{{ __('common.reset') }}</a>
            </div>
        </form>
    </x-filter-panel>

    <div class="card overflow-hidden">
        <div class="divide-y divide-grid md:hidden">
            @forelse($suppliers as $supplier)
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <a href="{{ route('suppliers.show', $supplier) }}" class="break-words text-sm font-semibold text-brand-900">{{ $supplier->name }}</a>
                        <span class="badge {{ $supplier->is_active ? 'bg-positive-50 text-positive-700 ring-positive-600/20' : 'bg-brand-50 text-brand-500 ring-brand-500/10' }}">{{ $supplier->is_active ? __('common.active') : __('common.inactive') }}</span>
                    </div>
                    <p class="code mt-0.5 text-[11px] text-brand-400">{{ $supplier->code }}</p>
                    @if($supplier->contact_name)<p class="mt-1 text-xs text-brand-500">{{ $supplier->contact_name }}</p>@endif
                    <div class="mt-3 flex items-center justify-end gap-1">
                        <a href="{{ route('suppliers.show', $supplier) }}" class="icon-btn" title="{{ __('common.view') }}"><x-icon name="eye" class="h-4 w-4" /></a>
                        @can('suppliers.manage')
                            <a href="{{ route('suppliers.edit', $supplier) }}" class="icon-btn" title="{{ __('common.edit') }}"><x-icon name="edit" class="h-4 w-4" /></a>
                            <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" data-confirm="{{ __('common.confirm_delete_text') }}">@csrf @method('DELETE')
                                <button type="submit" class="icon-btn hover:!bg-rose-50 hover:!text-rose-600" title="{{ __('common.delete') }}"><x-icon name="trash" class="h-4 w-4" /></button>
                            </form>
                        @endcan
                    </div>
                </div>
            @empty
                <x-empty-state icon="suppliers" :title="__('common.no_results')" :description="__('suppliers.subtitle')" />
            @endforelse
        </div>
        <div class="hidden md:block">
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr>
                    <x-sortable-th column="code" :sort="$sort" :direction="$direction">{{ __('fields.code') }}</x-sortable-th>
                    <x-sortable-th column="name" :sort="$sort" :direction="$direction">{{ __('fields.name') }}</x-sortable-th>
                    <th>{{ __('suppliers.contact_person') }}</th>
                    <th>{{ __('suppliers.expenses') }}</th>
                    <x-sortable-th column="is_active" :sort="$sort" :direction="$direction">{{ __('common.status') }}</x-sortable-th>
                    <th class="text-end">{{ __('common.actions') }}</th>
                </tr></thead>
                <tbody>
                @forelse($suppliers as $supplier)
                    <tr>
                        <td><span class="font-mono text-xs text-slate-500">{{ $supplier->code }}</span></td>
                        <td>
                            <a href="{{ route('suppliers.show', $supplier) }}" class="font-semibold text-slate-800 hover:text-brand-800">{{ $supplier->name }}</a>
                            @if($supplier->email)<p class="mt-0.5 text-[11px] text-slate-400">{{ $supplier->email }}</p>@endif
                        </td>
                        <td class="text-slate-500">{{ $supplier->contact_name ?: '—' }} @if($supplier->phone)<span class="block text-[11px] text-slate-400" dir="ltr">{{ $supplier->phone }}</span>@endif</td>
                        <td class="text-slate-500">{{ $supplier->expenses_count }}</td>
                        <td><span class="badge {{ $supplier->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-slate-100 text-slate-500 ring-slate-500/10' }}">{{ $supplier->is_active ? __('common.active') : __('common.inactive') }}</span></td>
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('suppliers.show', $supplier) }}" class="icon-btn" title="{{ __('common.view') }}"><x-icon name="eye" class="h-4 w-4" /></a>
                                @can('suppliers.manage')
                                    <a href="{{ route('suppliers.edit', $supplier) }}" class="icon-btn" title="{{ __('common.edit') }}"><x-icon name="edit" class="h-4 w-4" /></a>
                                    <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" data-confirm="{{ __('common.confirm_delete_text') }}">@csrf @method('DELETE')
                                        <button class="icon-btn hover:!bg-rose-50 hover:!text-rose-600" title="{{ __('common.delete') }}"><x-icon name="trash" class="h-4 w-4" /></button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-empty-state icon="suppliers" :title="__('common.no_results')" :description="__('suppliers.subtitle')" /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        </div>
        <x-pagination :paginator="$suppliers" />
    </div>
@endsection
