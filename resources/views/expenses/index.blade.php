{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', __('expenses.title'))
@section('content')
    <x-page-header :title="__('expenses.title')" :subtitle="__('expenses.subtitle')">
        <x-slot:actions>
            @can('expenses.manage')
                <a href="{{ route('expense-categories.index') }}" class="btn-secondary">{{ __('expenses.categories_title') }}</a>
                <a href="{{ route('expenses.create') }}" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> {{ __('expenses.create') }}</a>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-filter-panel>
        <form method="GET" action="{{ route('expenses.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-[1fr_170px_170px_150px_150px_auto]">
            <div class="relative"><span class="pointer-events-none absolute inset-y-0 start-3 flex items-center text-slate-400"><x-icon name="search" class="h-4 w-4" /></span><input type="search" name="q" value="{{ request('q') }}" class="input ps-10" placeholder="{{ __('expenses.search_placeholder') }}"></div>
            <select name="category" class="select"><option value="">{{ __('expenses.all_categories') }}</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->displayName() }}</option>@endforeach</select>
            <select name="supplier" class="select"><option value="">{{ __('expenses.all_suppliers') }}</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected((string) request('supplier') === (string) $supplier->id)>{{ $supplier->name }}</option>@endforeach</select>
            <x-date-input name="from" :value="request('from')" />
            <x-date-input name="to" :value="request('to')" />
            <div class="flex gap-2"><button class="btn-primary">{{ __('common.apply') }}</button><a href="{{ route('expenses.index') }}" class="btn-ghost">{{ __('common.reset') }}</a></div>
        </form>
    </x-filter-panel>

    <div class="card overflow-hidden">
        <div class="divide-y divide-grid md:hidden">
            @forelse($expenses as $expense)
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <a href="{{ route('expenses.show', $expense) }}" class="code text-sm font-bold text-brand-800">{{ $expense->number }}</a>
                        <span class="badge bg-brand-50 text-brand-600 ring-brand-500/10">{{ $expense->category?->displayName() }}</span>
                    </div>
                    <p class="mt-1 break-words text-sm font-semibold text-brand-800">{{ $expense->description }}</p>
                    <dl class="mt-2 grid grid-cols-2 gap-2 text-xs text-brand-500">
                        <div><dt class="text-[10px] text-brand-400">{{ __('expenses.expense_date') }}</dt><dd class="amount text-brand-700">{{ $expense->expense_date->format('Y-m-d') }}</dd></div>
                        <div class="text-end"><dt class="text-[10px] text-brand-400">{{ __('fields.amount') }}</dt><dd class="amount font-bold text-brand-900">{{ money($expense->amount_minor) }}</dd></div>
                    </dl>
                    <div class="mt-3 flex items-center justify-end gap-1">
                        <a href="{{ route('expenses.show', $expense) }}" class="icon-btn" title="{{ __('common.view') }}"><x-icon name="eye" class="h-4 w-4" /></a>
                        @can('expenses.manage')<a href="{{ route('expenses.edit', $expense) }}" class="icon-btn" title="{{ __('common.edit') }}"><x-icon name="edit" class="h-4 w-4" /></a>@endcan
                    </div>
                </div>
            @empty
                <x-empty-state icon="expenses" :title="__('common.no_results')" :description="__('expenses.no_expenses')" />
            @endforelse
        </div>
        <div class="hidden md:block">
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr>
                    <x-sortable-th column="number" :sort="$sort" :direction="$direction">{{ __('fields.number') }}</x-sortable-th>
                    <x-sortable-th column="expense_date" :sort="$sort" :direction="$direction">{{ __('expenses.expense_date') }}</x-sortable-th>
                    <th>{{ __('fields.category') }}</th>
                    <th>{{ __('fields.supplier') }}</th>
                    <th>{{ __('fields.description') }}</th>
                    <x-sortable-th column="amount_minor" :sort="$sort" :direction="$direction">{{ __('fields.amount') }}</x-sortable-th>
                    <th class="text-end">{{ __('common.actions') }}</th>
                </tr></thead>
                <tbody>
                @forelse($expenses as $expense)
                    <tr>
                        <td><a href="{{ route('expenses.show', $expense) }}" class="font-mono text-xs font-bold text-brand-800 hover:underline">{{ $expense->number }}</a></td>
                        <td class="whitespace-nowrap text-slate-500">{{ $expense->expense_date->format('Y-m-d') }}</td>
                        <td><span class="badge bg-slate-100 text-slate-600 ring-slate-500/10">{{ $expense->category?->displayName() }}</span></td>
                        <td class="text-slate-500">{{ $expense->supplier?->name ?? '—' }}</td>
                        <td><p class="max-w-xs truncate text-slate-600">{{ $expense->description }}</p></td>
                        <td class="whitespace-nowrap font-bold text-slate-800">{{ money($expense->amount_minor) }}</td>
                        <td><div class="flex justify-end gap-1">
                            <a href="{{ route('expenses.show', $expense) }}" class="icon-btn" title="{{ __('common.view') }}"><x-icon name="eye" class="h-4 w-4" /></a>
                            @can('expenses.manage')<a href="{{ route('expenses.edit', $expense) }}" class="icon-btn" title="{{ __('common.edit') }}"><x-icon name="edit" class="h-4 w-4" /></a>@endcan
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="7"><x-empty-state icon="expenses" :title="__('common.no_results')" :description="__('expenses.no_expenses')" /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        </div>
        <x-pagination :paginator="$expenses" />
    </div>
@endsection
