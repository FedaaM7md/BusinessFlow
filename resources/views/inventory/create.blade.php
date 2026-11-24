{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', __('inventory.new'))
@section('content')
    <x-page-header :title="__('inventory.new')" :subtitle="__('inventory.subtitle')">
        <x-slot:actions><a href="{{ route('inventory.index') }}" class="btn-secondary">{{ __('common.back') }}</a></x-slot:actions>
    </x-page-header>

    <form method="POST" action="{{ route('inventory.store') }}" x-data="{ type: '{{ old('type', 'in') }}' }">
        @csrf
        <div class="form-section">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="label" for="type">{{ __('fields.movement_type') }} <span class="text-rose-500">*</span></label>
                    <select id="type" name="type" x-model="type" class="select" required>
                        @foreach(['in','out','adjustment','transfer'] as $type)
                            <option value="{{ $type }}" @selected(old('type', 'in') === $type)>{{ __('statuses.'.$type) }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-[11px] text-slate-400">{{ __('inventory.type_hint') }}</p>
                    @error('type')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="moved_at">{{ __('inventory.movement_date') }} <span class="text-rose-500">*</span></label>
                    <x-date-input id="moved_at" name="moved_at" :value="old('moved_at', now()->format('Y-m-d'))" required />
                    @error('moved_at')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="label" for="product_id">{{ __('fields.product') }} <span class="text-rose-500">*</span></label>
                    <select id="product_id" name="product_id" class="select" required>
                        <option value="">{{ __('common.select') }}</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" @selected((string) old('product_id', request('product')) === (string) $product->id)>{{ $product->displayName() }} · {{ $product->sku }} ({{ $product->unit }})</option>
                        @endforeach
                    </select>
                    @error('product_id')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="warehouse_id">{{ __('fields.warehouse') }} <span class="text-rose-500">*</span></label>
                    <select id="warehouse_id" name="warehouse_id" class="select" required>
                        <option value="">{{ __('common.select') }}</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected((string) old('warehouse_id', $warehouses->firstWhere('is_default', true)?->id ?? $warehouses->first()?->id) === (string) $warehouse->id)>{{ $warehouse->name }}{{ $warehouse->is_default ? ' · '.__('inventory.default') : '' }}</option>
                        @endforeach
                    </select>
                    @error('warehouse_id')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div x-show="type === 'transfer'" x-cloak>
                    <label class="label" for="target_warehouse_id">{{ __('fields.target_warehouse') }} <span class="text-rose-500">*</span></label>
                    <select id="target_warehouse_id" name="target_warehouse_id" class="select" :required="type === 'transfer'">
                        <option value="">{{ __('common.select') }}</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected((string) old('target_warehouse_id') === (string) $warehouse->id)>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                    @error('target_warehouse_id')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="quantity">{{ __('fields.quantity') }} <span class="text-rose-500">*</span></label>
                    <input id="quantity" name="quantity" value="{{ old('quantity') }}" class="input" inputmode="decimal" required>
                    <p class="mt-1 text-[11px] text-slate-400" x-show="type === 'adjustment'">{{ __('inventory.quantity_hint') }}</p>
                    @error('quantity')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div x-show="type === 'in'" x-cloak>
                    <label class="label" for="unit_cost">{{ __('inventory.unit_cost') }}</label>
                    <input id="unit_cost" name="unit_cost" value="{{ old('unit_cost') }}" class="input" inputmode="decimal">
                    @error('unit_cost')<p class="error-text">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="label" for="note">{{ __('fields.notes') }}</label>
                    <textarea id="note" name="note" rows="3" class="input" placeholder="{{ __('inventory.note_placeholder') }}">{{ old('note') }}</textarea>
                    @error('note')<p class="error-text">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-2 border-t border-slate-100 pt-5">
                <a href="{{ route('inventory.index') }}" class="btn-secondary">{{ __('common.cancel') }}</a>
                <button class="btn-primary">{{ __('inventory.create') }}</button>
            </div>
        </div>
    </form>
@endsection
