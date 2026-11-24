{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@php($category = $category ?? null)
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <label class="label" for="name">{{ __('fields.name') }} <span class="text-rose-500">*</span></label>
        <input id="name" name="name" value="{{ old('name', $category?->name) }}" class="input" required>
        @error('name')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="label" for="name_ar">{{ __('fields.name_ar') }}</label>
        <input id="name_ar" name="name_ar" value="{{ old('name_ar', $category?->name_ar) }}" class="input">
        @error('name_ar')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div class="sm:col-span-2">
        <label class="label" for="description">{{ __('fields.description') }}</label>
        <textarea id="description" name="description" rows="3" class="input">{{ old('description', $category?->description) }}</textarea>
        @error('description')<p class="error-text">{{ $message }}</p>@enderror
    </div>
</div>
