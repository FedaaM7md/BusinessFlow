{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@php($warehouse = $warehouse ?? null)
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <label class="label" for="code">{{ __('inventory.warehouse_code') }} <span class="text-rose-500">*</span></label>
        <input id="code" name="code" value="{{ old('code', $warehouse?->code) }}" class="input" dir="ltr" required>
        @error('code')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="label" for="name">{{ __('fields.name') }} <span class="text-rose-500">*</span></label>
        <input id="name" name="name" value="{{ old('name', $warehouse?->name) }}" class="input" required>
        @error('name')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div class="sm:col-span-2">
        <label class="label" for="location">{{ __('inventory.warehouse_location') }}</label>
        <input id="location" name="location" value="{{ old('location', $warehouse?->location) }}" class="input">
        @error('location')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div>
        <input type="hidden" name="is_default" value="0">
        <label class="flex h-full items-start gap-3 rounded-xl border border-slate-200 p-4"><input type="checkbox" name="is_default" value="1" @checked(old('is_default', $warehouse?->is_default ?? false)) class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand-800"><span><span class="block text-sm font-semibold text-slate-700">{{ __('inventory.make_default') }}</span><span class="block text-xs text-slate-400">{{ __('inventory.default_hint') }}</span></span></label>
    </div>
    <div>
        <input type="hidden" name="is_active" value="0">
        <label class="flex h-full items-center gap-3 rounded-xl border border-slate-200 p-4"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $warehouse?->is_active ?? true)) class="h-4 w-4 rounded border-slate-300 text-brand-800">{{ __('fields.is_active') }}</label>
    </div>
</div>
