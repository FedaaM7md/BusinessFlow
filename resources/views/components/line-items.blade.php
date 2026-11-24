{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@props([
    'products',
    'lines' => [],
    'taxDefault' => '14',
    'title' => null,
    'hint' => null,
    'addLabel' => null,
    'selectLabel' => null,
])
@php
    $productData = $products->map(fn ($product) => [
        'id' => $product->id,
        'name' => $product->displayName(),
        'sku' => $product->sku,
        'label' => $product->displayName().' · '.$product->sku,
        'price' => money_input($product->price_minor),
        'tax_rate' => \App\Modules\Core\Services\Money::basisPointsToPercent($product->tax_rate),
    ])->values();
    $lineData = collect($lines)->map(fn ($line) => [
        'product_id' => $line['product_id'] ?? '',
        'description' => $line['description'] ?? '',
        'quantity' => $line['quantity'] ?? '1',
        'unit_price' => $line['unit_price'] ?? '0.00',
        'tax_rate' => $line['tax_rate'] ?? $taxDefault,
    ])->values();
@endphp
<div
    x-data="lineItems"
    data-lines="{{ json_encode($lineData->all(), JSON_UNESCAPED_UNICODE) }}"
    data-products="{{ json_encode($productData->all(), JSON_UNESCAPED_UNICODE) }}"
    data-tax="{{ (string) $taxDefault }}"
>
    <div class="flex items-center justify-between gap-3">
        <div>
            <h3 class="section-title">{{ $title ?? __('invoices.lines') }}</h3>
            <p class="muted mt-0.5">{{ $hint ?? __('invoices.lines_hint') }}</p>
        </div>
        <button type="button" class="btn-secondary !px-3 !py-2 text-xs" @click="addLine"><x-icon name="plus" class="h-4 w-4" /> {{ $addLabel ?? __('invoices.add_line') }}</button>
    </div>

    @error('lines')<p class="error-text mt-2">{{ $message }}</p>@enderror

    <div class="mt-4 space-y-3">
        <template x-for="line in lines" :key="line.key">
            <div class="rounded-lg border border-grid bg-brand-50/40 p-3">
                <div class="grid grid-cols-1 gap-3 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,2fr)_110px_130px_100px_130px_40px] lg:items-end">
                    <div>
                        <label class="mb-1 block text-[11px] font-semibold text-slate-500">{{ __('fields.product') }}</label>
                        <select :name="line.field + '[product_id]'" x-model="line.product_id" @change="pickProduct(line)" class="select !py-2 text-xs">
                            <option value="">{{ $selectLabel ?? __('quotations.select_product') }}</option>
                            <template x-for="product in products" :key="product.id">
                                <option :value="product.id" x-text="product.label"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-semibold text-slate-500">{{ __('fields.description') }}</label>
                        <input :name="line.field + '[description]'" x-model="line.description" class="input !py-2 text-xs" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-semibold text-slate-500">{{ __('fields.quantity') }}</label>
                        <input :name="line.field + '[quantity]'" x-model="line.quantity" @input="recalc(line)" type="number" min="0.01" step="0.01" class="input !py-2 text-xs" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-semibold text-slate-500">{{ __('fields.unit_price') }}</label>
                        <input :name="line.field + '[unit_price]'" x-model="line.unit_price" @input="recalc(line)" type="text" inputmode="decimal" class="input !py-2 text-xs" required>
                    </div>
                    <div>
                        <label class="mb-1 block text-[11px] font-semibold text-slate-500">{{ __('fields.tax_rate') }} %</label>
                        <input :name="line.field + '[tax_rate]'" x-model="line.tax_rate" @input="recalc(line)" type="number" min="0" max="100" step="0.01" class="input !py-2 text-xs" required>
                    </div>
                    <div class="pb-2">
                        <p class="text-[10px] font-semibold text-slate-400">{{ __('fields.total') }}</p>
                        <p class="mt-1 text-xs font-bold text-slate-700 amount" dir="ltr">≈ <span x-text="line.formatted_total"></span></p>
                    </div>
                    <button type="button" class="icon-btn mb-0.5 justify-self-end hover:!bg-rose-50 hover:!text-rose-600" @click="removeLine(line)" title="{{ __('quotations.remove_line') }}">
                        <x-icon name="trash" class="h-4 w-4" />
                    </button>
                </div>
            </div>
        </template>
    </div>
    <p class="mt-3 text-[11px] text-slate-400">{{ __('quotations.product_hint') }}</p>
</div>
