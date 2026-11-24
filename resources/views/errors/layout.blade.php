{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">
    <title>@yield('title') · {{ __('common.app_name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-paper px-5 py-10 text-brand-900 antialiased">
    <div class="w-full max-w-lg text-center">
        <x-logo class="mx-auto h-16 w-16 shadow-lg" />
        <p class="mt-8 text-7xl font-bold tracking-tight text-brand-900">@yield('code')</p>
        <h1 class="mt-3 text-2xl font-bold text-brand-900">@yield('title')</h1>
        <p class="mx-auto mt-3 max-w-md text-sm leading-7 text-brand-500">@yield('message')</p>
        <div class="mt-7 flex flex-wrap items-center justify-center gap-2">
            @auth
                <a href="{{ route('dashboard') }}" class="btn-primary">{{ __('errors.back_to_dashboard') }}</a>
            @endauth
            <button type="button" data-back class="btn-secondary">{{ __('errors.go_back') }}</button>
        </div>
        <div class="mt-8 space-y-1 text-xs text-brand-400">
            <p>{{ __('common.copyright') }}</p>
            <p><a href="https://fedaamohamed.com" rel="noopener" class="hover:text-brand-700">{{ __('common.credit') }}</a></p>
            <p class="text-[11px] text-brand-300">{{ __('common.demo_public_note') }}</p>
        </div>
    </div>
</body>
</html>
