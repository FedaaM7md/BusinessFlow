{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@php($user = $user ?? null)
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <label class="label" for="name">{{ __('fields.name') }} <span class="text-rose-500">*</span></label>
        <input id="name" name="name" value="{{ old('name', $user?->name) }}" class="input" required>
        @error('name')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="label" for="email">{{ __('fields.email') }} <span class="text-rose-500">*</span></label>
        <input id="email" type="email" name="email" value="{{ old('email', $user?->email) }}" class="input" dir="ltr" required>
        @error('email')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="label" for="role_id">{{ __('users.role') }} <span class="text-rose-500">*</span></label>
        <select id="role_id" name="role_id" class="select" required>
            <option value="">{{ __('users.select_role') }}</option>
            @foreach($roles as $role)
                <option value="{{ $role->id }}" @selected((string) old('role_id', $user?->role_id) === (string) $role->id)>{{ $role->displayName() }}</option>
            @endforeach
        </select>
        @error('role_id')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="label" for="locale">{{ __('fields.locale') }} <span class="text-rose-500">*</span></label>
        <select id="locale" name="locale" class="select" required>
            <option value="ar" @selected(old('locale', $user?->locale ?? 'ar') === 'ar')>{{ __('settings.language_arabic') }}</option>
            <option value="en" @selected(old('locale', $user?->locale) === 'en')>{{ __('settings.language_english') }}</option>
        </select>
        @error('locale')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="label" for="password">{{ $user ? __('fields.new_password') : __('fields.password') }} @if(!$user)<span class="text-rose-500">*</span>@endif</label>
        <input id="password" type="password" name="password" class="input" autocomplete="new-password" @if(!$user) required @endif>
        <p class="mt-1 text-[11px] text-slate-400">{{ $user ? __('fields.password_hint') : __('users.password_note') }}</p>
        @error('password')<p class="error-text">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="label" for="password_confirmation">{{ __('fields.password_confirmation') }}</label>
        <input id="password_confirmation" type="password" name="password_confirmation" class="input" autocomplete="new-password">
    </div>
    <div class="sm:col-span-2">
        <input type="hidden" name="is_active" value="0">
        <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-4"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user?->is_active ?? true)) class="h-4 w-4 rounded border-slate-300 text-brand-800"><span><span class="block text-sm font-semibold text-slate-700">{{ __('fields.is_active') }}</span><span class="block text-xs text-slate-400">{{ __('users.active_hint') }}</span></span></label>
    </div>
</div>
