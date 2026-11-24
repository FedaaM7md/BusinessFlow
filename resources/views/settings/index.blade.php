{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', __('settings.title'))
@section('content')
    <x-page-header :title="__('settings.title')" :subtitle="__('settings.subtitle')" />

    <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="space-y-6 xl:col-span-2">
                <section class="form-section">
                    <h2 class="section-title">{{ __('settings.company_profile') }}</h2>
                    <p class="muted mt-1">{{ __('settings.company_profile_hint') }}</p>
                    <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label class="label" for="company_name_ar">{{ __('fields.company_name_ar') }} <span class="text-rose-500">*</span></label>
                            <input id="company_name_ar" name="company_name_ar" value="{{ old('company_name_ar', $settings->company_name_ar) }}" class="input" required>
                            @error('company_name_ar')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="label" for="company_name_en">{{ __('fields.company_name_en') }} <span class="text-rose-500">*</span></label>
                            <input id="company_name_en" name="company_name_en" value="{{ old('company_name_en', $settings->company_name_en) }}" class="input" dir="ltr" required>
                            @error('company_name_en')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="label" for="address_ar">{{ __('fields.address_ar') }}</label>
                            <input id="address_ar" name="address_ar" value="{{ old('address_ar', $settings->address_ar) }}" class="input">
                            @error('address_ar')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="label" for="address_en">{{ __('fields.address_en') }}</label>
                            <input id="address_en" name="address_en" value="{{ old('address_en', $settings->address_en) }}" class="input" dir="ltr">
                            @error('address_en')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="label" for="phone">{{ __('fields.phone') }}</label>
                            <input id="phone" name="phone" value="{{ old('phone', $settings->phone) }}" class="input" dir="ltr">
                            @error('phone')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="label" for="email">{{ __('fields.email') }}</label>
                            <input id="email" type="email" name="email" value="{{ old('email', $settings->email) }}" class="input" dir="ltr">
                            @error('email')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="label" for="tax_number">{{ __('fields.tax_number') }}</label>
                            <input id="tax_number" name="tax_number" value="{{ old('tax_number', $settings->tax_number) }}" class="input" dir="ltr">
                            @error('tax_number')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="label" for="logo">{{ __('fields.logo') }}</label>
                            <input id="logo" type="file" name="logo" accept="image/png,image/jpeg,image/webp" class="input !py-2 text-xs">
                            <p class="mt-1 text-[11px] text-slate-400">{{ __('settings.logo_hint') }}</p>
                            @error('logo')<p class="error-text">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                <section class="form-section">
                    <h2 class="section-title">{{ __('settings.financial_defaults') }}</h2>
                    <p class="muted mt-1">{{ __('settings.financial_defaults_hint') }}</p>
                    <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        <div><label class="label" for="currency_code">{{ __('fields.currency_code') }}</label><input id="currency_code" name="currency_code" value="{{ old('currency_code', $settings->currency_code) }}" class="input" dir="ltr" maxlength="3" required><p class="mt-1 text-[11px] text-slate-400">{{ __('settings.currency_code_hint') }}</p>@error('currency_code')<p class="error-text">{{ $message }}</p>@enderror</div>
                        <div><label class="label" for="currency_symbol_ar">{{ __('fields.currency_symbol_ar') }}</label><input id="currency_symbol_ar" name="currency_symbol_ar" value="{{ old('currency_symbol_ar', $settings->currency_symbol_ar) }}" class="input" required>@error('currency_symbol_ar')<p class="error-text">{{ $message }}</p>@enderror</div>
                        <div><label class="label" for="currency_symbol_en">{{ __('fields.currency_symbol_en') }}</label><input id="currency_symbol_en" name="currency_symbol_en" value="{{ old('currency_symbol_en', $settings->currency_symbol_en) }}" class="input" dir="ltr" required>@error('currency_symbol_en')<p class="error-text">{{ $message }}</p>@enderror</div>
                        <div><label class="label" for="default_tax_rate">{{ __('fields.default_tax_rate') }}</label><input id="default_tax_rate" type="number" step="0.01" min="0" max="100" name="default_tax_rate" value="{{ old('default_tax_rate', \App\Modules\Core\Services\Money::basisPointsToPercent($settings->default_tax_rate)) }}" class="input" required><p class="mt-1 text-[11px] text-slate-400">{{ __('settings.tax_rate_hint') }}</p>@error('default_tax_rate')<p class="error-text">{{ $message }}</p>@enderror</div>
                    </div>
                </section>

                <section class="form-section">
                    <h2 class="section-title">{{ __('settings.numbering') }}</h2>
                    <p class="muted mt-1">{{ __('settings.numbering_hint') }}</p>
                    <div class="mt-5 grid grid-cols-2 gap-4 lg:grid-cols-4">
                        <div><label class="label" for="quotation_prefix">{{ __('fields.quotation_prefix') }}</label><input id="quotation_prefix" name="quotation_prefix" value="{{ old('quotation_prefix', $settings->quotation_prefix) }}" class="input" dir="ltr" required>@error('quotation_prefix')<p class="error-text">{{ $message }}</p>@enderror</div>
                        <div><label class="label" for="invoice_prefix">{{ __('fields.invoice_prefix') }}</label><input id="invoice_prefix" name="invoice_prefix" value="{{ old('invoice_prefix', $settings->invoice_prefix) }}" class="input" dir="ltr" required>@error('invoice_prefix')<p class="error-text">{{ $message }}</p>@enderror</div>
                        <div><label class="label" for="payment_prefix">{{ __('fields.payment_prefix') }}</label><input id="payment_prefix" name="payment_prefix" value="{{ old('payment_prefix', $settings->payment_prefix) }}" class="input" dir="ltr" required>@error('payment_prefix')<p class="error-text">{{ $message }}</p>@enderror</div>
                        <div><label class="label" for="expense_prefix">{{ __('fields.expense_prefix') }}</label><input id="expense_prefix" name="expense_prefix" value="{{ old('expense_prefix', $settings->expense_prefix) }}" class="input" dir="ltr" required>@error('expense_prefix')<p class="error-text">{{ $message }}</p>@enderror</div>
                    </div>
                </section>

                <section class="form-section">
                    <h2 class="section-title">{{ __('settings.localization') }}</h2>
                    <p class="muted mt-1">{{ __('settings.localization_hint') }}</p>
                    <div class="mt-5 max-w-sm">
                        <label class="label" for="default_locale">{{ __('fields.default_locale') }}</label>
                        <select id="default_locale" name="default_locale" class="select" required>
                            <option value="ar" @selected(old('default_locale', $settings->default_locale) === 'ar')>{{ __('settings.language_arabic') }}</option>
                            <option value="en" @selected(old('default_locale', $settings->default_locale) === 'en')>{{ __('settings.language_english') }}</option>
                        </select>
                        @error('default_locale')<p class="error-text">{{ $message }}</p>@enderror
                    </div>
                </section>
            </div>

            <aside class="space-y-6">
                <section class="card p-5">
                    <h2 class="section-title">{{ __('settings.company_logo') }}</h2>
                    <div class="mt-4 flex min-h-40 items-center justify-center rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4">
                        @if($settings->logo_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($settings->logo_path) }}" alt="{{ __('settings.company_logo') }}" class="max-h-32 max-w-full object-contain">
                        @else
                            <div class="text-center text-slate-400"><x-logo class="mx-auto h-14 w-14" /><p class="mt-2 text-xs">{{ __('fields.current_logo') }}: —</p></div>
                        @endif
                    </div>
                </section>
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs leading-6 text-amber-800">
                    <p class="font-bold">{{ __('settings.danger_zone') }}</p>
                    <p class="mt-1">{{ __('settings.demo_notice') }}</p>
                </div>
                <button class="btn-primary w-full">{{ __('common.save_changes') }}</button>
            </aside>
        </div>
    </form>

    @if($settings->logo_path)
        <form method="POST" action="{{ route('settings.logo.remove') }}" class="mt-5" data-confirm="{{ __('common.confirm_delete_text') }}">
            @csrf @method('DELETE')
            <button class="btn-ghost !text-rose-600"><x-icon name="trash" class="h-4 w-4" /> {{ __('settings.remove_logo') }}</button>
        </form>
    @endif
@endsection
