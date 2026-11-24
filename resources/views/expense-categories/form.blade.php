{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@php($expenseCategory = $expenseCategory ?? null)
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <label class="label" for="name">{{ __('expenses.category_name') }} <span class="text-rose-500">*</span></label>
        <input id="name" name="name" value="{{ old('name', $expenseCategory?->name) }}" class="input" required>
        @error('name')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="label" for="name_ar">{{ __('fields.name_ar') }}</label>
        <input id="name_ar" name="name_ar" value="{{ old('name_ar', $expenseCategory?->name_ar) }}" class="input">
        @error('name_ar')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div class="sm:col-span-2">
        <input type="hidden" name="is_active" value="0">
        <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-4"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $expenseCategory?->is_active ?? true)) class="h-4 w-4 rounded border-slate-300 text-brand-800">{{ __('fields.is_active') }}</label>
    </div>
</div>
