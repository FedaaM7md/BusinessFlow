{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@php
    $quotation = $quotation ?? null;
    $defaultTax = \App\Modules\Core\Services\Money::basisPointsToPercent(company()->default_tax_rate);
    $lineSource = old('lines', $quotation ? $quotation->lines->map(fn ($line) => [
        'product_id' => $line->product_id,
        'description' => $line->description,
        'quantity' => quantity($line->quantity),
        'unit_price' => money_input($line->unit_price_minor),
        'tax_rate' => \App\Modules\Core\Services\Money::basisPointsToPercent($line->tax_rate),
    ])->all() : []);
@endphp
<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    <div class="space-y-6 xl:col-span-2">
        <div class="form-section">
            <h2 class="section-title">{{ __('quotations.lines') }}</h2>
            <div class="mt-5">
                <x-line-items
                    :products="$products"
                    :lines="$lineSource"
                    :tax-default="$defaultTax"
                    :title="__('quotations.lines')"
                    :hint="__('quotations.lines_hint')"
                    :add-label="__('quotations.add_line')"
                    :select-label="__('quotations.select_product')"
                />
            </div>
        </div>
        <div class="form-section">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="label" for="notes">{{ __('fields.notes') }}</label>
                    <textarea id="notes" name="notes" rows="3" class="input">{{ old('notes', $quotation?->notes) }}</textarea>
                    @error('notes')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="label" for="terms">{{ __('fields.terms') }}</label>
                    <textarea id="terms" name="terms" rows="4" class="input">{{ old('terms', $quotation?->terms) }}</textarea>
                    @error('terms')<p class="error-text">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
    </div>

    <aside class="space-y-6">
        <div class="form-section">
            <h2 class="section-title">{{ __('quotations.summary') }}</h2>
            <div class="mt-5 space-y-5">
                <div>
                    <label class="label" for="customer_id">{{ __('fields.customer') }} <span class="text-rose-500">*</span></label>
                    <select id="customer_id" name="customer_id" class="select" required>
                        <option value="">{{ __('common.select') }}</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" @selected((string) old('customer_id', $quotation?->customer_id ?? request('customer')) === (string) $customer->id)>{{ $customer->name }} · {{ $customer->code }}</option>
                        @endforeach
                    </select>
                    @error('customer_id')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label" for="issue_date">{{ __('quotations.issue_date') }}</label>
                        <x-date-input id="issue_date" name="issue_date" :value="old('issue_date', $quotation?->issue_date?->format('Y-m-d') ?? now()->format('Y-m-d'))" required />
                        @error('issue_date')<p class="error-text">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label" for="valid_until">{{ __('quotations.valid_until') }}</label>
                        <x-date-input id="valid_until" name="valid_until" :value="old('valid_until', $quotation?->valid_until?->format('Y-m-d') ?? now()->addDays(14)->format('Y-m-d'))" />
                        @error('valid_until')<p class="error-text">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label class="label" for="discount">{{ __('fields.discount') }}</label>
                    <input id="discount" name="discount" value="{{ old('discount', money_input($quotation?->discount_minor ?? 0)) }}" class="input" inputmode="decimal">
                    <p class="mt-1 text-[11px] text-slate-400">{{ __('quotations.discount_hint') }}</p>
                    @error('discount')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div class="rounded-xl bg-slate-50 p-4 text-xs leading-6 text-slate-500">
                    <p class="font-bold text-slate-700">{{ __('quotations.validity') }}</p>
                    <p>{{ __('quotations.expires_on', ['date' => $quotation?->valid_until?->format('Y-m-d') ?? '—']) }}</p>
                </div>
            </div>
        </div>
        <div class="flex flex-col gap-2">
            <button type="submit" class="btn-primary w-full">{{ __('common.save') }}</button>
            <a href="{{ $quotation ? route('quotations.show', $quotation) : route('quotations.index') }}" class="btn-secondary w-full">{{ __('common.cancel') }}</a>
        </div>
    </aside>
</div>
