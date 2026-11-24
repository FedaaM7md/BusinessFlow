{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@php($product = $product ?? null)
<div x-data="{ type: '{{ old('type', $product?->type ?? 'product') }}' }" class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    <div class="space-y-6 xl:col-span-2">
        <div class="form-section">
            <h2 class="section-title">{{ __('products.profile') }}</h2>
            <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="label" for="sku">{{ __('fields.sku') }} <span class="text-rose-500">*</span></label>
                    <input id="sku" name="sku" value="{{ old('sku', $product?->sku) }}" class="input" dir="ltr" required>
                    @error('sku')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="type">{{ __('fields.type') }} <span class="text-rose-500">*</span></label>
                    <select id="type" name="type" x-model="type" class="select" required>
                        <option value="product">{{ __('products.product') }}</option>
                        <option value="service">{{ __('products.service') }}</option>
                    </select>
                    @error('type')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="name">{{ __('fields.name') }} <span class="text-rose-500">*</span></label>
                    <input id="name" name="name" value="{{ old('name', $product?->name) }}" class="input" required>
                    @error('name')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="name_ar">{{ __('fields.name_ar') }}</label>
                    <input id="name_ar" name="name_ar" value="{{ old('name_ar', $product?->name_ar) }}" class="input">
                    @error('name_ar')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="category_id">{{ __('fields.category') }}</label>
                    <select id="category_id" name="category_id" class="select">
                        <option value="">{{ __('common.none') }}</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) old('category_id', $product?->category_id) === (string) $category->id)>{{ $category->displayName() }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="unit">{{ __('fields.unit') }} <span class="text-rose-500">*</span></label>
                    <input id="unit" name="unit" value="{{ old('unit', $product?->unit ?? 'unit') }}" class="input" placeholder="{{ __('products.unit_examples') }}" required>
                    @error('unit')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="label" for="notes">{{ __('fields.notes') }}</label>
                    <textarea id="notes" name="notes" rows="3" class="input">{{ old('notes', $product?->notes) }}</textarea>
                    @error('notes')<p class="error-text">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="form-section">
            <h2 class="section-title">{{ __('products.inventory_settings') }}</h2>
            <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <input type="hidden" name="is_tracked" value="0">
                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-4" :class="{ 'opacity-50': type === 'service' }">
                        <input type="checkbox" name="is_tracked" value="1" @checked(old('is_tracked', $product?->is_tracked ?? true)) :disabled="type === 'service'" class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand-800">
                        <span><span class="block text-sm font-semibold text-slate-700">{{ __('fields.tracked') }}</span><span class="block text-xs leading-5 text-slate-400">{{ __('products.tracked_hint') }}</span></span>
                    </label>
                    @error('is_tracked')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="low_stock_threshold">{{ __('fields.low_stock_threshold') }}</label>
                    <input id="low_stock_threshold" type="number" step="0.01" min="0" name="low_stock_threshold" value="{{ old('low_stock_threshold', $product?->low_stock_threshold ?? 5) }}" class="input">
                    <p class="mt-1 text-[11px] text-slate-400">{{ __('products.low_threshold_hint') }}</p>
                    @error('low_stock_threshold')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-end">
                    <input type="hidden" name="is_active" value="0">
                    <label class="flex w-full items-center gap-3 rounded-xl border border-slate-200 p-4"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product?->is_active ?? true)) class="h-4 w-4 rounded border-slate-300 text-brand-800">{{ __('fields.is_active') }}</label>
                </div>
            </div>
        </div>
    </div>

    <aside class="space-y-6">
        <div class="form-section">
            <h2 class="section-title">{{ __('products.pricing') }}</h2>
            <div class="mt-5 space-y-5">
                <div>
                    <label class="label" for="price">{{ __('fields.price') }} <span class="text-rose-500">*</span></label>
                    <input id="price" name="price" value="{{ old('price', $product ? money_input($product->price_minor) : '') }}" class="input" inputmode="decimal" required>
                    @error('price')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="cost">{{ __('fields.cost') }}</label>
                    <input id="cost" name="cost" value="{{ old('cost', $product ? money_input($product->cost_minor) : '0.00') }}" class="input" inputmode="decimal">
                    @error('cost')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="tax_rate">{{ __('fields.tax_rate') }} (%)</label>
                    <input id="tax_rate" type="number" step="0.01" min="0" max="100" name="tax_rate" value="{{ old('tax_rate', \App\Modules\Core\Services\Money::basisPointsToPercent($product?->tax_rate ?? company()->default_tax_rate)) }}" class="input" required>
                    @error('tax_rate')<p class="error-text">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
        <div class="flex flex-col gap-2">
            <button class="btn-primary w-full">{{ __('common.save') }}</button>
            <a href="{{ $product ? route('products.show', $product) : route('products.index') }}" class="btn-secondary w-full">{{ __('common.cancel') }}</a>
        </div>
    </aside>
</div>
