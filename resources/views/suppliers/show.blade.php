{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', $supplier->name)
@section('content')
    <x-page-header :title="$supplier->name" :subtitle="$supplier->code . ($supplier->contact_name ? ' · '.$supplier->contact_name : '')">
        <x-slot:actions>
            @can('suppliers.manage')
                <a href="{{ route('suppliers.edit', $supplier) }}" class="btn-secondary"><x-icon name="edit" class="h-4 w-4" /> {{ __('common.edit') }}</a>
            @endcan
            @can('expenses.manage')
                <a href="{{ route('expenses.create', ['supplier' => $supplier->id]) }}" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> {{ __('expenses.create') }}</a>
            @endcan
            <a href="{{ route('suppliers.index') }}" class="btn-ghost">{{ __('common.back') }}</a>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <section class="card p-5 sm:p-6">
            <div class="flex items-center justify-between"><h2 class="section-title">{{ __('suppliers.profile') }}</h2><span class="badge {{ $supplier->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-slate-100 text-slate-500 ring-slate-500/10' }}">{{ $supplier->is_active ? __('common.active') : __('common.inactive') }}</span></div>
            <dl class="mt-5 space-y-4 text-sm">
                <div><dt class="text-xs font-semibold text-slate-400">{{ __('fields.contact_name') }}</dt><dd class="mt-0.5 font-medium text-slate-700">{{ $supplier->contact_name ?: '—' }}</dd></div>
                <div><dt class="text-xs font-semibold text-slate-400">{{ __('fields.email') }}</dt><dd class="mt-0.5 font-medium text-slate-700">{{ $supplier->email ?: '—' }}</dd></div>
                <div><dt class="text-xs font-semibold text-slate-400">{{ __('fields.phone') }}</dt><dd class="mt-0.5 font-medium text-slate-700" dir="ltr">{{ $supplier->phone ?: '—' }}</dd></div>
                <div><dt class="text-xs font-semibold text-slate-400">{{ __('fields.tax_number') }}</dt><dd class="mt-0.5 font-medium text-slate-700">{{ $supplier->tax_number ?: '—' }}</dd></div>
                <div><dt class="text-xs font-semibold text-slate-400">{{ __('fields.address') }}</dt><dd class="mt-0.5 font-medium text-slate-700">{{ collect([$supplier->address, $supplier->city])->filter()->implode('، ') ?: '—' }}</dd></div>
            </dl>
        </section>

        <section class="card overflow-hidden xl:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div><h2 class="section-title">{{ __('suppliers.expenses') }}</h2><p class="muted mt-0.5">{{ __('suppliers.expense_count', ['count' => $expenses->total()]) }}</p></div>
                <div class="text-end"><p class="text-xs text-slate-400">{{ __('suppliers.expense_total') }}</p><p class="text-lg font-bold text-slate-900">{{ money($expenseTotal) }}</p></div>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>{{ __('fields.code') }}</th><th>{{ __('fields.date') }}</th><th>{{ __('fields.expense_category') }}</th><th class="text-end">{{ __('fields.amount') }}</th><th></th></tr></thead>
                    <tbody>
                    @forelse($expenses as $expense)
                        <tr>
                            <td class="font-mono text-xs">{{ $expense->number }}</td>
                            <td>{{ $expense->expense_date->format('Y-m-d') }}</td>
                            <td>{{ $expense->category?->displayName() }}</td>
                            <td class="text-end font-semibold">{{ money($expense->amount_minor) }}</td>
                            <td class="text-end"><a href="{{ route('expenses.show', $expense) }}" class="text-xs font-bold text-brand-800 hover:underline">{{ __('common.view') }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-empty-state icon="expenses" :title="__('suppliers.no_expenses')" :description="__('suppliers.subtitle')" /></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <x-pagination :paginator="$expenses" />
        </section>
    </div>
@endsection
