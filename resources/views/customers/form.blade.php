{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@php($customer = $customer ?? null)
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <label for="type" class="label">{{ __('customers.type') }} <span class="text-rose-500">*</span></label>
        <select id="type" name="type" class="select @error('type') !border-rose-400 @enderror" required>
            <option value="company" @selected(old('type', $customer?->type ?? 'company') === 'company')>{{ __('customers.company') }}</option>
            <option value="individual" @selected(old('type', $customer?->type) === 'individual')>{{ __('customers.individual') }}</option>
        </select>
        @error('type')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="name" class="label">{{ __('fields.name') }} <span class="text-rose-500">*</span></label>
        <input id="name" name="name" value="{{ old('name', $customer?->name) }}" class="input @error('name') !border-rose-400 @enderror" required>
        @error('name')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="company_name" class="label">{{ __('fields.company_name') }} <span class="text-slate-400">({{ __('common.optional') }})</span></label>
        <input id="company_name" name="company_name" value="{{ old('company_name', $customer?->company_name) }}" class="input @error('company_name') !border-rose-400 @enderror">
        @error('company_name')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="tax_number" class="label">{{ __('fields.tax_number') }} <span class="text-slate-400">({{ __('common.optional') }})</span></label>
        <input id="tax_number" name="tax_number" value="{{ old('tax_number', $customer?->tax_number) }}" class="input" dir="ltr">
        @error('tax_number')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="email" class="label">{{ __('fields.email') }}</label>
        <input id="email" type="email" name="email" value="{{ old('email', $customer?->email) }}" class="input" dir="ltr">
        @error('email')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="phone" class="label">{{ __('fields.phone') }}</label>
        <input id="phone" name="phone" value="{{ old('phone', $customer?->phone) }}" class="input" dir="ltr">
        @error('phone')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="address" class="label">{{ __('fields.address') }}</label>
        <input id="address" name="address" value="{{ old('address', $customer?->address) }}" class="input">
        @error('address')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="city" class="label">{{ __('fields.city') }}</label>
        <input id="city" name="city" value="{{ old('city', $customer?->city) }}" class="input">
        @error('city')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="country" class="label">{{ __('fields.country') }}</label>
        <input id="country" name="country" value="{{ old('country', $customer?->country) }}" class="input">
        @error('country')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="notes" class="label">{{ __('fields.notes') }}</label>
        <textarea id="notes" name="notes" rows="3" class="input">{{ old('notes', $customer?->notes) }}</textarea>
        @error('notes')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div class="sm:col-span-2">
        <input type="hidden" name="is_active" value="0">
        <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-4">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $customer?->is_active ?? true)) class="h-4 w-4 rounded border-slate-300 text-brand-800 focus:ring-brand-500">
            <span>
                <span class="block text-sm font-semibold text-slate-700">{{ __('fields.is_active') }}</span>
                <span class="block text-xs text-slate-400">{{ __('customers.inactive_hint') }}</span>
            </span>
        </label>
    </div>
</div>
