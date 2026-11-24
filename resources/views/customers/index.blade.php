{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')

@section('title', __('customers.title'))

@section('content')
    <x-page-header :title="__('customers.title')" :subtitle="__('customers.subtitle')">
        <x-slot:actions>
            @can('customers.manage')
                <a href="{{ route('customers.create') }}" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> {{ __('customers.create') }}</a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-filter-panel>
        <form method="GET" action="{{ route('customers.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_180px_180px_auto]">
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 start-3 flex items-center text-slate-400"><x-icon name="search" class="h-4 w-4" /></span>
                <input type="search" name="q" value="{{ request('q') }}" class="input ps-10" placeholder="{{ __('customers.search_placeholder') }}">
            </div>
            <select name="type" class="select">
                <option value="">{{ __('customers.all_types') }}</option>
                <option value="company" @selected(request('type') === 'company')>{{ __('customers.company') }}</option>
                <option value="individual" @selected(request('type') === 'individual')>{{ __('customers.individual') }}</option>
            </select>
            <select name="status" class="select">
                <option value="">{{ __('common.all') }}</option>
                <option value="active" @selected(request('status') === 'active')>{{ __('common.active') }}</option>
                <option value="inactive" @selected(request('status') === 'inactive')>{{ __('common.inactive') }}</option>
            </select>
            <div class="flex items-center gap-2">
                <button type="submit" class="btn-primary">{{ __('common.apply') }}</button>
                <a href="{{ route('customers.index') }}" class="btn-ghost">{{ __('common.reset') }}</a>
            </div>
        </form>
    </x-filter-panel>

    <div class="card overflow-hidden">
        <div class="divide-y divide-grid md:hidden">
            @forelse($customers as $customer)
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <a href="{{ route('customers.show', $customer) }}" class="break-words text-sm font-semibold text-brand-900">{{ $customer->name }}</a>
                        <span class="badge {{ $customer->is_active ? 'bg-positive-50 text-positive-700 ring-positive-600/20' : 'bg-brand-50 text-brand-500 ring-brand-500/10' }}">{{ $customer->is_active ? __('common.active') : __('common.inactive') }}</span>
                    </div>
                    <p class="code mt-0.5 text-[11px] text-brand-400">{{ $customer->code }}</p>
                    <div class="mt-3 flex items-center justify-between gap-2">
                        <span class="badge bg-brand-50 text-brand-600 ring-brand-500/10">{{ __('customers.'.$customer->type) }}</span>
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('customers.show', $customer) }}" class="icon-btn" title="{{ __('common.view') }}"><x-icon name="eye" class="h-4 w-4" /></a>
                            @can('customers.manage')
                                <a href="{{ route('customers.edit', $customer) }}" class="icon-btn" title="{{ __('common.edit') }}"><x-icon name="edit" class="h-4 w-4" /></a>
                                <form method="POST" action="{{ route('customers.destroy', $customer) }}" data-confirm="{{ __('common.confirm_delete_text') }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="icon-btn hover:!bg-rose-50 hover:!text-rose-600" title="{{ __('common.delete') }}"><x-icon name="trash" class="h-4 w-4" /></button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </div>
            @empty
                <x-empty-state icon="customers" :title="__('common.no_results')" :description="__('customers.subtitle')" />
            @endforelse
        </div>
        <div class="hidden md:block">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <x-sortable-th column="code" :sort="$sort" :direction="$direction">{{ __('fields.code') }}</x-sortable-th>
                        <x-sortable-th column="name" :sort="$sort" :direction="$direction">{{ __('fields.name') }}</x-sortable-th>
                        <th>{{ __('fields.type') }}</th>
                        <th>{{ __('customers.contacts') }}</th>
                        <th>{{ __('fields.invoices') }}</th>
                        <x-sortable-th column="is_active" :sort="$sort" :direction="$direction">{{ __('common.status') }}</x-sortable-th>
                        <th class="text-end">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($customers as $customer)
                    <tr>
                        <td><span class="font-mono text-xs text-slate-500">{{ $customer->code }}</span></td>
                        <td>
                            <a href="{{ route('customers.show', $customer) }}" class="font-semibold text-slate-800 hover:text-brand-800">{{ $customer->name }}</a>
                            @if($customer->company_name)<p class="mt-0.5 text-[11px] text-slate-400">{{ $customer->company_name }}</p>@endif
                        </td>
                        <td><span class="badge bg-slate-100 text-slate-600 ring-slate-500/10">{{ __('customers.'.$customer->type) }}</span></td>
                        <td class="text-slate-500">{{ $customer->contacts_count }}</td>
                        <td class="text-slate-500">{{ $customer->invoices_count }}</td>
                        <td>
                            <span class="badge {{ $customer->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-slate-100 text-slate-500 ring-slate-500/10' }}">
                                {{ $customer->is_active ? __('common.active') : __('common.inactive') }}
                            </span>
                        </td>
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('customers.show', $customer) }}" class="icon-btn" title="{{ __('common.view') }}"><x-icon name="eye" class="h-4 w-4" /></a>
                                @can('customers.manage')
                                    <a href="{{ route('customers.edit', $customer) }}" class="icon-btn" title="{{ __('common.edit') }}"><x-icon name="edit" class="h-4 w-4" /></a>
                                    <form method="POST" action="{{ route('customers.destroy', $customer) }}" data-confirm="{{ __('common.confirm_delete_text') }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="icon-btn hover:!bg-rose-50 hover:!text-rose-600" title="{{ __('common.delete') }}"><x-icon name="trash" class="h-4 w-4" /></button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><x-empty-state icon="customers" :title="__('common.no_results')" :description="__('customers.subtitle')" /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        </div>
        <x-pagination :paginator="$customers" />
    </div>
@endsection
