{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', __('payments.new'))
@section('content')
    @php
        $selected = $invoices->firstWhere('id', (int) old('invoice_id', $invoice?->id));
        $selectedBalance = $selected?->balanceMinor() ?? 0;
    @endphp
    <x-page-header :title="__('payments.new')" :subtitle="__('payments.record_for_hint')">
        <x-slot:actions><a href="{{ route('payments.index') }}" class="btn-secondary">{{ __('common.back') }}</a></x-slot:actions>
    </x-page-header>

    @if($invoices->isEmpty())
        <div class="card"><x-empty-state icon="payments" :title="__('payments.no_invoices')" :description="__('messages.no_payable_invoices')">
            <x-slot:action><a href="{{ route('invoices.index') }}" class="btn-primary">{{ __('invoices.title') }}</a></x-slot:action>
        </x-empty-state></div>
    @else
        <form method="POST" action="{{ route('payments.store') }}">
            @csrf
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                <div class="form-section xl:col-span-2">
                    <h2 class="section-title">{{ __('payments.payment_detail') }}</h2>
                    <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="label" for="invoice_id">{{ __('payments.available') }} <span class="text-rose-500">*</span></label>
                            <select id="invoice_id" name="invoice_id" class="select" required>
                                <option value="">{{ __('payments.choose_invoice') }}</option>
                                @foreach($invoices as $item)
                                    <option value="{{ $item->id }}" @selected((string) old('invoice_id', $invoice?->id) === (string) $item->id)>{{ $item->number }} · {{ $item->customer?->name }} · {{ money($item->balanceMinor()) }} {{ __('payments.invoice_balance') }}</option>
                                @endforeach
                            </select>
                            @error('invoice_id')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="label" for="amount">{{ __('payments.amount') }} <span class="text-rose-500">*</span></label>
                            <input id="amount" name="amount" value="{{ old('amount', $selectedBalance ? money_input($selectedBalance) : '') }}" class="input" inputmode="decimal" required>
                            @if($selected)<p class="mt-1 text-[11px] text-slate-400">{{ __('payments.max_hint', ['amount' => money($selectedBalance)]) }}</p>@endif
                            @error('amount')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="label" for="payment_date">{{ __('payments.date') }} <span class="text-rose-500">*</span></label>
                            <x-date-input id="payment_date" name="payment_date" :value="old('payment_date', now()->format('Y-m-d'))" :max="now()->format('Y-m-d')" required />
                            @error('payment_date')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="label" for="method">{{ __('payments.method') }} <span class="text-rose-500">*</span></label>
                            <select id="method" name="method" class="select" required>
                                @foreach(\App\Modules\Sales\Models\Payment::METHODS as $method)
                                    <option value="{{ $method }}" @selected(old('method', 'cash') === $method)>{{ __('statuses.'.$method) }}</option>
                                @endforeach
                            </select>
                            @error('method')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="label" for="reference">{{ __('fields.reference') }}</label>
                            <input id="reference" name="reference" value="{{ old('reference') }}" class="input" placeholder="{{ __('fields.reference') }}">
                            @error('reference')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="label" for="notes">{{ __('fields.notes') }}</label>
                            <textarea id="notes" name="notes" rows="3" class="input">{{ old('notes') }}</textarea>
                            @error('notes')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-2 border-t border-slate-100 pt-5">
                        <a href="{{ route('payments.index') }}" class="btn-secondary">{{ __('common.cancel') }}</a>
                        <button class="btn-primary">{{ __('payments.create') }}</button>
                    </div>
                </div>

                <aside class="space-y-4">
                    @if($selected)
                        <div class="card p-5">
                            <h2 class="section-title">{{ __('fields.invoice') }}</h2>
                            <div class="mt-4 space-y-2 text-sm">
                                <p class="font-mono font-bold text-brand-800">{{ $selected->number }}</p>
                                <p class="font-semibold text-slate-700">{{ $selected->customer?->name }}</p>
                                <div class="flex justify-between border-t border-slate-100 pt-2"><span class="text-slate-500">{{ __('fields.total') }}</span><span>{{ money($selected->total_minor) }}</span></div>
                                <div class="flex justify-between"><span class="text-slate-500">{{ __('fields.paid') }}</span><span class="text-emerald-700">{{ money($selected->paidMinor()) }}</span></div>
                                <div class="flex justify-between text-base font-bold"><span>{{ __('payments.invoice_balance') }}</span><span class="text-rose-600">{{ money($selectedBalance) }}</span></div>
                            </div>
                            <div class="mt-4"><x-status-badge :status="$selected->status" /></div>
                        </div>
                    @endif
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs leading-6 text-amber-800">
                        <p class="font-bold">{{ __('common.info') }}</p>
                        <p>{{ __('payments.immutable_note') }}</p>
                    </div>
                </aside>
            </div>
        </form>
    @endif
@endsection
