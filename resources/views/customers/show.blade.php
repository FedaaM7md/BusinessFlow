{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')

@section('title', $customer->name)

@section('content')
    <x-page-header :title="$customer->name" :subtitle="$customer->code . ($customer->company_name ? ' · '.$customer->company_name : '')">
        <x-slot:actions>
            @can('customers.manage')
                <a href="{{ route('customers.edit', $customer) }}" class="btn-secondary"><x-icon name="edit" class="h-4 w-4" /> {{ __('common.edit') }}</a>
            @endcan
            @can('invoices.manage')
                <a href="{{ route('invoices.create', ['customer' => $customer->id]) }}" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> {{ __('invoices.create') }}</a>
            @endcan
            <a href="{{ route('customers.index') }}" class="btn-ghost">{{ __('common.back') }}</a>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <section class="card p-5 sm:p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="section-title">{{ __('customers.profile') }}</h2>
                    <span class="badge {{ $customer->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-slate-100 text-slate-500 ring-slate-500/10' }}">{{ $customer->is_active ? __('common.active') : __('common.inactive') }}</span>
                </div>
                <dl class="mt-5 grid grid-cols-1 gap-x-8 gap-y-4 text-sm sm:grid-cols-2">
                    <div><dt class="text-xs font-semibold text-slate-400">{{ __('fields.type') }}</dt><dd class="mt-1 font-medium text-slate-700">{{ __('customers.'.$customer->type) }}</dd></div>
                    <div><dt class="text-xs font-semibold text-slate-400">{{ __('fields.email') }}</dt><dd class="mt-1 font-medium text-slate-700">{{ $customer->email ?: '—' }}</dd></div>
                    <div><dt class="text-xs font-semibold text-slate-400">{{ __('fields.phone') }}</dt><dd class="mt-1 font-medium text-slate-700" dir="ltr">{{ $customer->phone ?: '—' }}</dd></div>
                    <div><dt class="text-xs font-semibold text-slate-400">{{ __('fields.tax_number') }}</dt><dd class="mt-1 font-medium text-slate-700">{{ $customer->tax_number ?: '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-xs font-semibold text-slate-400">{{ __('fields.address') }}</dt><dd class="mt-1 font-medium text-slate-700">{{ collect([$customer->address, $customer->city, $customer->country])->filter()->implode('، ') ?: '—' }}</dd></div>
                    @if($customer->notes)<div class="sm:col-span-2"><dt class="text-xs font-semibold text-slate-400">{{ __('fields.notes') }}</dt><dd class="mt-1 whitespace-pre-line text-slate-600">{{ $customer->notes }}</dd></div>@endif
                </dl>
            </section>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="card p-5"><p class="kpi-label">{{ __('customers.invoiced_total') }}</p><p class="mt-1 text-xl font-bold text-slate-900">{{ money($totals['invoiced']) }}</p></div>
                <div class="card p-5"><p class="kpi-label">{{ __('customers.paid_total') }}</p><p class="mt-1 text-xl font-bold text-emerald-700">{{ money($totals['paid']) }}</p></div>
                <div class="card p-5"><p class="kpi-label">{{ __('customers.balance_due') }}</p><p class="mt-1 text-xl font-bold {{ $totals['balance'] > 0 ? 'text-rose-600' : 'text-slate-900' }}">{{ money($totals['balance']) }}</p></div>
            </div>

            <section class="card overflow-hidden">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="section-title">{{ __('customers.statement') }}</h2>
                    <p class="muted mt-0.5">{{ __('customers.statement_subtitle') }}</p>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead><tr><th>{{ __('customers.statement_date') }}</th><th>{{ __('customers.reference') }}</th><th>{{ __('fields.type') }}</th><th class="text-end">{{ __('customers.debit') }}</th><th class="text-end">{{ __('customers.credit') }}</th><th class="text-end">{{ __('fields.balance') }}</th></tr></thead>
                        <tbody>
                        @php($running = 0)
                        @forelse($statement as $row)
                            @php($running += $row['debit'] - $row['credit'])
                            <tr>
                                <td class="whitespace-nowrap text-slate-500">{{ $row['date']->format('Y-m-d') }}</td>
                                <td><a href="{{ $row['link'] }}" class="font-semibold text-brand-800 hover:underline">{{ $row['number'] }}</a></td>
                                <td><span class="badge {{ $row['type'] === 'invoice' ? 'bg-sky-50 text-sky-700 ring-sky-600/20' : 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' }}">{{ $row['type'] === 'invoice' ? __('customers.debit') : __('customers.credit') }}</span></td>
                                <td class="whitespace-nowrap text-end">{{ $row['debit'] ? money($row['debit']) : '—' }}</td>
                                <td class="whitespace-nowrap text-end text-emerald-700">{{ $row['credit'] ? money($row['credit']) : '—' }}</td>
                                <td class="whitespace-nowrap text-end font-semibold">{{ money($running) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-empty-state icon="invoices" :title="__('customers.no_statement')" :description="__('customers.statement_subtitle')" /></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="card overflow-hidden">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div>
                        <h2 class="section-title">{{ __('customers.contacts') }}</h2>
                        <p class="muted mt-0.5">{{ __('customers.contacts_count', ['count' => $customer->contacts->count()]) }}</p>
                    </div>
                    @can('customers.manage')
                        <a href="{{ route('contacts.create', $customer) }}" class="icon-btn bg-brand-50 text-brand-800" title="{{ __('customers.add_contact') }}"><x-icon name="plus" class="h-4 w-4" /></a>
                    @endcan
                </div>
                @if($customer->contacts->isEmpty())
                    <x-empty-state icon="users" :title="__('customers.no_contacts')" :description="__('customers.no_contacts_hint')" />
                @else
                    <ul class="divide-y divide-slate-100">
                        @foreach($customer->contacts as $contact)
                            <li class="px-5 py-4">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="flex items-center gap-2 text-sm font-bold text-slate-800">{{ $contact->name }} @if($contact->is_primary)<span class="badge bg-accent-50 text-accent-700 ring-accent-600/20">{{ __('customers.primary') }}</span>@endif</p>
                                        @if($contact->position)<p class="text-xs text-slate-400">{{ $contact->position }}</p>@endif
                                        @if($contact->email)<p class="mt-1 truncate text-xs text-slate-500" dir="ltr">{{ $contact->email }}</p>@endif
                                        @if($contact->phone)<p class="text-xs text-slate-500" dir="ltr">{{ $contact->phone }}</p>@endif
                                    </div>
                                    @can('customers.manage')
                                        <div class="flex shrink-0 items-center gap-1">
                                            <a href="{{ route('contacts.edit', [$customer, $contact]) }}" class="icon-btn" title="{{ __('common.edit') }}"><x-icon name="edit" class="h-4 w-4" /></a>
                                            <form method="POST" action="{{ route('contacts.destroy', [$customer, $contact]) }}" data-confirm="{{ __('common.confirm_delete_text') }}">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="icon-btn hover:!bg-rose-50 hover:!text-rose-600" title="{{ __('common.delete') }}"><x-icon name="trash" class="h-4 w-4" /></button>
                                            </form>
                                        </div>
                                    @endcan
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <section class="card overflow-hidden">
                <div class="border-b border-slate-100 px-5 py-4"><h2 class="section-title">{{ __('customers.recent_invoices') }}</h2></div>
                @if($invoices->isEmpty())
                    <x-empty-state icon="invoices" :title="__('customers.no_statement')" :description="__('customers.statement_subtitle')" />
                @else
                    <ul class="divide-y divide-slate-100">
                        @foreach($invoices->take(6) as $invoice)
                            <li class="flex items-center justify-between gap-3 px-5 py-3">
                                <div class="min-w-0">
                                    <a href="{{ route('invoices.show', $invoice) }}" class="text-sm font-semibold text-slate-700 hover:text-brand-800">{{ $invoice->number }}</a>
                                    <p class="text-[11px] text-slate-400">{{ $invoice->issue_date->format('Y-m-d') }}</p>
                                </div>
                                <x-status-badge :status="$invoice->status" />
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </aside>
    </div>
@endsection
