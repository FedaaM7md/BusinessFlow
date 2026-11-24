{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@php($supplier = $supplier ?? null)
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <label for="name" class="label">{{ __('fields.name') }} <span class="text-rose-500">*</span></label>
        <input id="name" name="name" value="{{ old('name', $supplier?->name) }}" class="input" required>
        @error('name')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="contact_name" class="label">{{ __('fields.contact_name') }}</label>
        <input id="contact_name" name="contact_name" value="{{ old('contact_name', $supplier?->contact_name) }}" class="input">
        @error('contact_name')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="email" class="label">{{ __('fields.email') }}</label>
        <input id="email" type="email" name="email" value="{{ old('email', $supplier?->email) }}" class="input" dir="ltr">
        @error('email')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="phone" class="label">{{ __('fields.phone') }}</label>
        <input id="phone" name="phone" value="{{ old('phone', $supplier?->phone) }}" class="input" dir="ltr">
        @error('phone')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="tax_number" class="label">{{ __('fields.tax_number') }}</label>
        <input id="tax_number" name="tax_number" value="{{ old('tax_number', $supplier?->tax_number) }}" class="input" dir="ltr">
        @error('tax_number')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="city" class="label">{{ __('fields.city') }}</label>
        <input id="city" name="city" value="{{ old('city', $supplier?->city) }}" class="input">
        @error('city')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="address" class="label">{{ __('fields.address') }}</label>
        <input id="address" name="address" value="{{ old('address', $supplier?->address) }}" class="input">
        @error('address')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="notes" class="label">{{ __('fields.notes') }}</label>
        <textarea id="notes" name="notes" rows="3" class="input">{{ old('notes', $supplier?->notes) }}</textarea>
        @error('notes')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div class="sm:col-span-2">
        <input type="hidden" name="is_active" value="0">
        <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-4">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $supplier?->is_active ?? true)) class="h-4 w-4 rounded border-slate-300 text-brand-800 focus:ring-brand-500">
            <span class="text-sm font-semibold text-slate-700">{{ __('fields.is_active') }}</span>
        </label>
    </div>
</div>
