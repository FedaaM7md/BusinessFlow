{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', $quotation->number)
@section('content')
    <x-page-header :title="$quotation->number" :subtitle="__('quotations.quote_for').' '.$quotation->customer?->name">
        <x-slot:actions>
            <x-status-badge :status="$quotation->status" />
            <a href="{{ route('quotations.print', $quotation) }}" target="_blank" class="btn-secondary"><x-icon name="print" class="h-4 w-4" /> {{ __('common.print') }}</a>
            @if($quotation->isDraft())
                @can('quotations.manage')<a href="{{ route('quotations.edit', $quotation) }}" class="btn-secondary"><x-icon name="edit" class="h-4 w-4" /> {{ __('common.edit') }}</a>@endcan
            @endif
            <a href="{{ route('quotations.index') }}" class="btn-ghost">{{ __('common.back') }}</a>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <section class="card overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                    <div><h2 class="section-title">{{ __('quotations.lines') }}</h2><p class="muted mt-0.5">{{ $quotation->issue_date->format('Y-m-d') }} @if($quotation->valid_until) · {{ __('quotations.expires_on', ['date' => $quotation->valid_until->format('Y-m-d')]) }} @endif</p></div>
                </div>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead><tr><th>#</th><th>{{ __('fields.description') }}</th><th class="text-end">{{ __('fields.quantity') }}</th><th class="text-end">{{ __('fields.unit_price') }}</th><th class="text-end">{{ __('fields.tax') }}</th><th class="text-end">{{ __('fields.total') }}</th></tr></thead>
                        <tbody>
                        @foreach($quotation->lines as $line)
                            <tr>
                                <td class="text-slate-400">{{ $loop->iteration }}</td>
                                <td><p class="font-medium text-slate-700">{{ $line->description }}</p>@if($line->product)<p class="text-[11px] text-slate-400">{{ $line->product->sku }}</p>@endif</td>
                                <td class="text-end">{{ quantity($line->quantity) }}</td>
                                <td class="text-end">{{ money($line->unit_price_minor) }}</td>
                                <td class="text-end text-slate-500">{{ \App\Modules\Core\Services\Money::basisPointsToPercent($line->tax_rate) }}%</td>
                                <td class="text-end font-semibold">{{ money($line->total_minor) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="flex justify-end border-t border-slate-100 p-5">
                    <dl class="w-full max-w-sm space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-slate-500">{{ __('fields.subtotal') }}</dt><dd>{{ money($quotation->subtotal_minor) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">{{ __('fields.discount') }}</dt><dd class="text-rose-600">− {{ money($quotation->discount_minor) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">{{ __('fields.tax') }}</dt><dd>{{ money($quotation->tax_minor) }}</dd></div>
                        <div class="flex justify-between border-t border-slate-200 pt-2 text-base font-bold"><dt>{{ __('fields.total') }}</dt><dd class="text-brand-800">{{ money($quotation->total_minor) }}</dd></div>
                    </dl>
                </div>
            </section>

            @if($quotation->notes || $quotation->terms)
                <section class="card p-5 sm:p-6">
                    @if($quotation->notes)<div><h3 class="text-sm font-bold text-slate-800">{{ __('fields.notes') }}</h3><p class="mt-1 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $quotation->notes }}</p></div>@endif
                    @if($quotation->terms)<div class="mt-5 border-t border-slate-100 pt-4"><h3 class="text-sm font-bold text-slate-800">{{ __('fields.terms') }}</h3><p class="mt-1 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $quotation->terms }}</p></div>@endif
                </section>
            @endif
        </div>

        <aside class="space-y-6">
            <section class="card p-5">
                <h2 class="section-title">{{ __('quotations.status_actions') }}</h2>
                <div class="mt-4 flex flex-col gap-2">
                    @if($quotation->status === 'draft')
                        @can('quotations.manage')
                            <form method="POST" action="{{ route('quotations.status', $quotation) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="sent"><button class="btn-primary w-full">{{ __('quotations.send') }}</button></form>
                        @endcan
                    @elseif($quotation->status === 'sent')
                        @can('quotations.manage')
                            <form method="POST" action="{{ route('quotations.status', $quotation) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="accepted"><button class="btn-accent w-full">{{ __('quotations.accept') }}</button></form>
                            <form method="POST" action="{{ route('quotations.status', $quotation) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="rejected"><button class="btn-danger w-full">{{ __('quotations.reject') }}</button></form>
                            <form method="POST" action="{{ route('quotations.status', $quotation) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="expired"><button class="btn-secondary w-full">{{ __('quotations.expire') }}</button></form>
                        @endcan
                    @elseif($quotation->status === 'accepted')
                        @if($quotation->invoice)
                            <a href="{{ route('invoices.show', $quotation->invoice) }}" class="btn-secondary w-full">{{ __('quotations.converted') }} · {{ $quotation->invoice->number }}</a>
                        @else
                            @can('invoices.manage')
                                <form method="POST" action="{{ route('quotations.convert', $quotation) }}" data-confirm="{{ __('quotations.convert_confirm') }}">@csrf<button class="btn-accent w-full">{{ __('quotations.convert') }}</button></form>
                            @endcan
                        @endif
                    @else
                        <p class="rounded-xl bg-slate-50 p-4 text-xs leading-6 text-slate-500">{{ __('quotations.only_accepted') }}</p>
                    @endif
                </div>
            </section>

            <section class="card p-5">
                <h2 class="section-title">{{ __('fields.customer') }}</h2>
                <div class="mt-3 space-y-1 text-sm">
                    <a href="{{ route('customers.show', $quotation->customer) }}" class="font-bold text-slate-800 hover:text-brand-800">{{ $quotation->customer?->name }}</a>
                    <p class="text-xs text-slate-400">{{ $quotation->customer?->code }}</p>
                    @if($quotation->customer?->email)<p class="text-xs text-slate-500">{{ $quotation->customer->email }}</p>@endif
                    @if($quotation->customer?->phone)<p class="text-xs text-slate-500" dir="ltr">{{ $quotation->customer->phone }}</p>@endif
                </div>
                <dl class="mt-5 space-y-3 border-t border-slate-100 pt-4 text-xs">
                    <div class="flex justify-between gap-2"><dt class="text-slate-400">{{ __('common.created_by') }}</dt><dd class="font-medium text-slate-600">{{ $quotation->creator?->name ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-2"><dt class="text-slate-400">{{ __('common.created_at') }}</dt><dd class="font-medium text-slate-600">{{ $quotation->created_at->format('Y-m-d') }}</dd></div>
                    <div class="flex justify-between gap-2"><dt class="text-slate-400">{{ __('fields.valid_until') }}</dt><dd class="font-medium text-slate-600">{{ $quotation->valid_until?->format('Y-m-d') ?? '—' }}</dd></div>
                </dl>
            </section>

            @if($quotation->isDraft())
                <form method="POST" action="{{ route('quotations.destroy', $quotation) }}" data-confirm="{{ __('common.confirm_delete_text') }}">
                    @csrf @method('DELETE')
                    <button class="btn-ghost w-full !text-rose-600"><x-icon name="trash" class="h-4 w-4" /> {{ __('common.delete') }}</button>
                </form>
            @endif
        </aside>
    </div>
@endsection
