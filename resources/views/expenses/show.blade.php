{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', $expense->number)
@section('content')
    <x-page-header :title="__('expenses.detail')" :subtitle="$expense->number">
        <x-slot:actions>
            @can('expenses.manage')<a href="{{ route('expenses.edit', $expense) }}" class="btn-secondary"><x-icon name="edit" class="h-4 w-4" /> {{ __('common.edit') }}</a>@endcan
            <a href="{{ route('expenses.index') }}" class="btn-ghost">{{ __('common.back') }}</a>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="card p-6 xl:col-span-2">
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-5">
                <div><p class="text-xs font-semibold uppercase tracking-widest text-slate-400">{{ __('expenses.title') }}</p><p class="mt-2 text-3xl font-bold text-slate-900">{{ money($expense->amount_minor) }}</p></div>
                <span class="badge bg-slate-100 text-slate-600 ring-slate-500/10">{{ $expense->category?->displayName() }}</span>
            </div>
            <dl class="mt-5 grid grid-cols-1 gap-x-8 gap-y-5 text-sm sm:grid-cols-2">
                <div><dt class="text-xs font-semibold text-slate-400">{{ __('fields.date') }}</dt><dd class="mt-1 font-medium text-slate-700">{{ $expense->expense_date->format('Y-m-d') }}</dd></div>
                <div><dt class="text-xs font-semibold text-slate-400">{{ __('fields.method') }}</dt><dd class="mt-1 font-medium text-slate-700">{{ __('statuses.'.$expense->payment_method) }}</dd></div>
                <div><dt class="text-xs font-semibold text-slate-400">{{ __('fields.supplier') }}</dt><dd class="mt-1 font-medium text-slate-700">@if($expense->supplier)<a href="{{ route('suppliers.show', $expense->supplier) }}" class="text-brand-800 hover:underline">{{ $expense->supplier->name }}</a>@else — @endif</dd></div>
                <div><dt class="text-xs font-semibold text-slate-400">{{ __('fields.reference') }}</dt><dd class="mt-1 font-medium text-slate-700">{{ $expense->reference ?: '—' }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-xs font-semibold text-slate-400">{{ __('fields.description') }}</dt><dd class="mt-1 whitespace-pre-line leading-7 text-slate-700">{{ $expense->description }}</dd></div>
            </dl>
        </div>
        <aside class="card p-5">
            <h2 class="section-title">{{ __('common.details') }}</h2>
            <dl class="mt-4 space-y-3 text-xs">
                <div class="flex justify-between gap-2"><dt class="text-slate-400">{{ __('common.created_by') }}</dt><dd class="font-medium text-slate-600">{{ $expense->creator?->name ?? '—' }}</dd></div>
                <div class="flex justify-between gap-2"><dt class="text-slate-400">{{ __('common.created_at') }}</dt><dd class="font-medium text-slate-600">{{ $expense->created_at->format('Y-m-d H:i') }}</dd></div>
                <div class="flex justify-between gap-2"><dt class="text-slate-400">{{ __('fields.number') }}</dt><dd class="font-mono font-medium text-slate-600">{{ $expense->number }}</dd></div>
            </dl>
            @can('expenses.manage')
                <form method="POST" action="{{ route('expenses.destroy', $expense) }}" class="mt-6 border-t border-slate-100 pt-4" data-confirm="{{ __('common.confirm_delete_text') }}">
                    @csrf @method('DELETE')
                    <button class="btn-ghost w-full !text-rose-600"><x-icon name="trash" class="h-4 w-4" /> {{ __('common.delete') }}</button>
                </form>
            @endcan
        </aside>
    </div>
@endsection
