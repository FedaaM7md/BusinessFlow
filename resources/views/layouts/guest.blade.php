{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ __('common.app_description') }}">
    @include('partials.meta', ['defaultTitle' => __('auth.login_title')])
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-paper text-brand-900 antialiased">
<div class="min-h-screen lg:grid lg:grid-cols-[1.05fr_1fr]">
    <section class="relative hidden min-w-0 flex-col justify-between border-e border-grid bg-paper p-10 lg:flex xl:p-14">
        <div class="flex items-center gap-3">
            <x-logo class="h-10 w-10" />
            <div>
                <p class="text-sm font-bold text-brand-900">{{ __('common.app_name') }}</p>
                <p class="text-[11px] text-brand-400">{{ __('common.app_descriptor') }}</p>
            </div>
        </div>

        <div class="my-8">
            <h1 class="max-w-md text-2xl font-bold leading-snug tracking-tight text-brand-900 xl:text-3xl">{{ __('auth.hero_title') }}</h1>
            <p class="mt-3 max-w-md text-sm leading-7 text-brand-500">{{ __('auth.hero_subtitle') }}</p>
        </div>

        <div class="mx-auto w-full max-w-md">
            @include('partials.login-invoice-preview', ['preview' => $preview ?? null])
        </div>

        <div class="mt-8 flex items-center justify-between gap-4 text-[11px] text-brand-400">
            <span>{{ __('common.copyright') }}</span>
            <span>{{ __('common.footer_tagline') }}</span>
        </div>
    </section>

    <section class="flex min-w-0 flex-col px-5 py-8 sm:px-10 lg:px-16">
        <div class="flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-3 lg:hidden">
                <x-logo class="h-9 w-9" />
                <span class="text-sm font-bold text-brand-900">{{ __('common.app_name') }}</span>
            </a>
            <div class="ms-auto">
                <form method="POST" action="{{ route('locale.update') }}">
                    @csrf
                    <input type="hidden" name="locale" value="{{ app()->getLocale() === 'ar' ? 'en' : 'ar' }}">
                    <button type="submit" class="btn-secondary !px-3 !py-2 text-xs">
                        <x-icon name="globe" class="h-4 w-4" />
                        {{ app()->getLocale() === 'ar' ? __('common.english') : __('common.arabic') }}
                    </button>
                </form>
            </div>
        </div>

        <div class="flex w-full flex-1 items-center justify-center py-10">
            <div class="w-full min-w-0 max-w-md">
                @include('partials.flash')
                @yield('content')
            </div>
        </div>

        <div class="text-center text-xs text-brand-400">
            <p>{{ __('common.copyright') }}</p>
            <p class="mt-1"><a href="https://fedaamohamed.com" rel="noopener" class="hover:text-brand-700">{{ __('common.credit') }}</a></p>
            <p class="mt-1 text-[11px] text-brand-300">
                <a href="{{ route('about') }}" class="hover:text-brand-700">{{ __('common.about') }}</a>
                <span aria-hidden="true"> · </span>
                <a href="{{ route('privacy') }}" class="hover:text-brand-700">{{ __('common.privacy') }}</a>
                <span aria-hidden="true"> · </span>
                <a href="{{ route('terms') }}" class="hover:text-brand-700">{{ __('common.terms') }}</a>
                <span aria-hidden="true"> · </span>
                {{ __('common.demo_public_note') }}
            </p>
        </div>
    </section>
</div>
</body>
</html>
