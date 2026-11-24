{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@php($contact = $contact ?? null)
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <label for="name" class="label">{{ __('fields.name') }} <span class="text-rose-500">*</span></label>
        <input id="name" name="name" value="{{ old('name', $contact?->name) }}" class="input" required>
        @error('name')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="position" class="label">{{ __('fields.position') }}</label>
        <input id="position" name="position" value="{{ old('position', $contact?->position) }}" class="input">
        @error('position')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="email" class="label">{{ __('fields.email') }}</label>
        <input id="email" type="email" name="email" value="{{ old('email', $contact?->email) }}" class="input" dir="ltr">
        @error('email')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="phone" class="label">{{ __('fields.phone') }}</label>
        <input id="phone" name="phone" value="{{ old('phone', $contact?->phone) }}" class="input" dir="ltr">
        @error('phone')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div class="sm:col-span-2">
        <input type="hidden" name="is_primary" value="0">
        <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-4">
            <input type="checkbox" name="is_primary" value="1" @checked(old('is_primary', $contact?->is_primary ?? false)) class="h-4 w-4 rounded border-slate-300 text-brand-800 focus:ring-brand-500">
            <span class="text-sm font-semibold text-slate-700">{{ __('fields.is_primary') }}</span>
        </label>
    </div>
</div>
