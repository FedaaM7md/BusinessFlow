{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', __('quotations.title'))
@section('content')
    <x-page-header :title="__('quotations.title')" :subtitle="__('quotations.subtitle')">
        <x-slot:actions>@can('quotations.manage')<a href="{{ route('quotations.create') }}" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> {{ __('quotations.create') }}</a>@endcan</x-slot:actions>
    </x-page-header>

    <x-filter-panel>
        <form method="GET" action="{{ route('quotations.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-[1fr_170px_200px_150px_150px_auto]">
            <div class="relative"><span class="pointer-events-none absolute inset-y-0 start-3 flex items-center text-slate-400"><x-icon name="search" class="h-4 w-4" /></span><input type="search" name="q" value="{{ request('q') }}" class="input ps-10" placeholder="{{ __('quotations.search_placeholder') }}"></div>
            <select name="status" class="select"><option value="">{{ __('quotations.all_statuses') }}</option>@foreach(['draft','sent','accepted','rejected','expired'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ __('statuses.'.$status) }}</option>@endforeach</select>
            <select name="customer" class="select"><option value="">{{ __('quotations.all_customers') }}</option>@foreach($customers as $customer)<option value="{{ $customer->id }}" @selected((string) request('customer') === (string) $customer->id)>{{ $customer->name }}</option>@endforeach</select>
            <x-date-input name="from" :value="request('from')" />
            <x-date-input name="to" :value="request('to')" />
            <div class="flex gap-2"><button class="btn-primary">{{ __('common.apply') }}</button><a href="{{ route('quotations.index') }}" class="btn-ghost">{{ __('common.reset') }}</a></div>
        </form>
    </x-filter-panel>

    <div class="card overflow-hidden">
        <div class="divide-y divide-grid md:hidden">
            @forelse($quotations as $quotation)
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <a href="{{ route('quotations.show', $quotation) }}" class="code text-sm font-bold text-brand-800">{{ $quotation->number }}</a>
                        <x-status-badge :status="$quotation->status" />
                    </div>
                    <p class="mt-1 break-words text-sm font-semibold text-brand-800">{{ $quotation->customer?->name }}</p>
                    <dl class="mt-2 grid grid-cols-2 gap-2 text-xs text-brand-500">
                        <div><dt class="text-[10px] text-brand-400">{{ __('quotations.issue_date') }}</dt><dd class="amount text-brand-700">{{ $quotation->issue_date->format('Y-m-d') }}</dd></div>
                        <div class="text-end"><dt class="text-[10px] text-brand-400">{{ __('fields.total') }}</dt><dd class="amount font-bold text-brand-900">{{ money($quotation->total_minor) }}</dd></div>
                    </dl>
                    <div class="mt-3 flex items-center justify-between gap-2">
                        <span class="text-xs text-brand-400">{{ __('quotations.valid_until') }}: <span class="amount">{{ $quotation->valid_until?->format('Y-m-d') ?? '—' }}</span></span>
                        <div class="flex items-center gap-1">
                            <a href="{{ route('quotations.show', $quotation) }}" class="icon-btn" title="{{ __('common.view') }}"><x-icon name="eye" class="h-4 w-4" /></a>
                            <a href="{{ route('quotations.print', $quotation) }}" target="_blank" class="icon-btn" title="{{ __('common.print') }}"><x-icon name="print" class="h-4 w-4" /></a>
                            @if($quotation->isDraft())
                                @can('quotations.manage')<a href="{{ route('quotations.edit', $quotation) }}" class="icon-btn" title="{{ __('common.edit') }}"><x-icon name="edit" class="h-4 w-4" /></a>@endcan
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <x-empty-state icon="quotations" :title="__('common.no_results')" :description="__('quotations.subtitle')" />
            @endforelse
        </div>
        <div class="hidden md:block">
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr>
                    <x-sortable-th column="number" :sort="$sort" :direction="$direction">{{ __('fields.number') }}</x-sortable-th>
                    <th>{{ __('fields.customer') }}</th>
                    <x-sortable-th column="issue_date" :sort="$sort" :direction="$direction">{{ __('quotations.issue_date') }}</x-sortable-th>
                    <th>{{ __('quotations.valid_until') }}</th>
                    <x-sortable-th column="total_minor" :sort="$sort" :direction="$direction">{{ __('fields.total') }}</x-sortable-th>
                    <x-sortable-th column="status" :sort="$sort" :direction="$direction">{{ __('common.status') }}</x-sortable-th>
                    <th class="text-end">{{ __('common.actions') }}</th>
                </tr></thead>
                <tbody>
                @forelse($quotations as $quotation)
                    <tr>
                        <td><a href="{{ route('quotations.show', $quotation) }}" class="font-mono text-xs font-bold text-brand-800 hover:underline">{{ $quotation->number }}</a></td>
                        <td><p class="font-semibold text-slate-700">{{ $quotation->customer?->name }}</p><p class="text-[11px] text-slate-400">{{ $quotation->customer?->company_name }}</p></td>
                        <td class="whitespace-nowrap text-slate-500">{{ $quotation->issue_date->format('Y-m-d') }}</td>
                        <td class="whitespace-nowrap text-slate-500">{{ $quotation->valid_until?->format('Y-m-d') ?? '—' }}</td>
                        <td class="whitespace-nowrap font-bold text-slate-800">{{ money($quotation->total_minor) }}</td>
                        <td><x-status-badge :status="$quotation->status" /></td>
                        <td><div class="flex items-center justify-end gap-1">
                            <a href="{{ route('quotations.show', $quotation) }}" class="icon-btn" title="{{ __('common.view') }}"><x-icon name="eye" class="h-4 w-4" /></a>
                            <a href="{{ route('quotations.print', $quotation) }}" target="_blank" class="icon-btn" title="{{ __('common.print') }}"><x-icon name="print" class="h-4 w-4" /></a>
                            @if($quotation->isDraft())
                                @can('quotations.manage')<a href="{{ route('quotations.edit', $quotation) }}" class="icon-btn" title="{{ __('common.edit') }}"><x-icon name="edit" class="h-4 w-4" /></a>@endcan
                            @endif
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="7"><x-empty-state icon="quotations" :title="__('common.no_results')" :description="__('quotations.subtitle')" /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        </div>
        <x-pagination :paginator="$quotations" />
    </div>
@endsection
