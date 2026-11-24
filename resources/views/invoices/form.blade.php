{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@php
    $invoice = $invoice ?? null;
    $defaultTax = \App\Modules\Core\Services\Money::basisPointsToPercent(company()->default_tax_rate);
    $lineSource = old('lines', $invoice ? $invoice->lines->map(fn ($line) => [
        'product_id' => $line->product_id,
        'description' => $line->description,
        'quantity' => quantity($line->quantity),
        'unit_price' => money_input($line->unit_price_minor),
        'tax_rate' => \App\Modules\Core\Services\Money::basisPointsToPercent($line->tax_rate),
    ])->all() : []);
    $defaultWarehouse = $warehouses->firstWhere('is_default', true)?->id ?? $warehouses->first()?->id;
@endphp
<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    <div class="space-y-6 xl:col-span-2">
        <div class="form-section">
            <x-line-items
                :products="$products"
                :lines="$lineSource"
                :tax-default="$defaultTax"
                :title="__('invoices.lines')"
                :hint="__('invoices.lines_hint')"
                :add-label="__('invoices.add_line')"
                :select-label="__('invoices.select_product')"
            />
        </div>
        <div class="form-section">
            <label class="label" for="notes">{{ __('fields.notes') }}</label>
            <textarea id="notes" name="notes" rows="3" class="input">{{ old('notes', $invoice?->notes) }}</textarea>
            @error('notes')<p class="error-text">{{ $message }}</p>@enderror
        </div>
    </div>

    <aside class="space-y-6">
        <div class="form-section">
            <h2 class="section-title">{{ __('invoices.summary') }}</h2>
            <div class="mt-5 space-y-5">
                <div>
                    <label class="label" for="customer_id">{{ __('fields.customer') }} <span class="text-rose-500">*</span></label>
                    <select id="customer_id" name="customer_id" class="select" required>
                        <option value="">{{ __('common.select') }}</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" @selected((string) old('customer_id', $invoice?->customer_id ?? request('customer')) === (string) $customer->id)>{{ $customer->name }} · {{ $customer->code }}</option>
                        @endforeach
                    </select>
                    @error('customer_id')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="warehouse_id">{{ __('invoices.warehouse') }}</label>
                    <select id="warehouse_id" name="warehouse_id" class="select">
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected((string) old('warehouse_id', $invoice?->warehouse_id ?? $defaultWarehouse) === (string) $warehouse->id)>{{ $warehouse->name }}{{ $warehouse->is_default ? ' · '.__('inventory.default') : '' }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-[11px] text-slate-400">{{ __('invoices.warehouse_hint') }}</p>
                    @error('warehouse_id')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label" for="issue_date">{{ __('invoices.issue_date') }}</label>
                        <x-date-input id="issue_date" name="issue_date" :value="old('issue_date', $invoice?->issue_date?->format('Y-m-d') ?? now()->format('Y-m-d'))" required />
                        @error('issue_date')<p class="error-text">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label" for="due_date">{{ __('invoices.due_date') }}</label>
                        <x-date-input id="due_date" name="due_date" :value="old('due_date', $invoice?->due_date?->format('Y-m-d') ?? now()->addDays(30)->format('Y-m-d'))" />
                        @error('due_date')<p class="error-text">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label class="label" for="discount">{{ __('fields.discount') }}</label>
                    <input id="discount" name="discount" value="{{ old('discount', money_input($invoice?->discount_minor ?? 0)) }}" class="input" inputmode="decimal">
                    <p class="mt-1 text-[11px] text-slate-400">{{ __('quotations.discount_hint') }}</p>
                    @error('discount')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div class="rounded-xl bg-amber-50 p-4 text-xs leading-6 text-amber-800">
                    <p class="font-bold">{{ __('invoices.stock_effects') }}</p>
                    <p>{{ __('invoices.stock_effects_hint') }}</p>
                </div>
            </div>
        </div>
        <div class="flex flex-col gap-2">
            <button type="submit" class="btn-primary w-full">{{ __('common.save') }}</button>
            <a href="{{ $invoice ? route('invoices.show', $invoice) : route('invoices.index') }}" class="btn-secondary w-full">{{ __('common.cancel') }}</a>
        </div>
    </aside>
</div>
