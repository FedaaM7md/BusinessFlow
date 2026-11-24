{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.meta', ['defaultTitle' => __('common.app_name')])
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-paper text-brand-900 antialiased">
    <header class="border-b border-grid bg-white/95 backdrop-blur">
        <div class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-3 px-4 py-4 sm:px-6">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <x-logo class="h-9 w-9" />
                <span>
                    <span class="block text-sm font-bold text-brand-900">{{ __('common.app_name') }}</span>
                    <span class="block text-[11px] text-brand-400">{{ __('common.app_descriptor') }}</span>
                </span>
            </a>
            <nav class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-brand-600" aria-label="{{ __('nav.primary') }}">
                <a href="{{ route('about') }}" class="hidden hover:text-brand-900 sm:inline">{{ __('common.about') }}</a>
                <a href="{{ route('privacy') }}" class="hidden hover:text-brand-900 sm:inline">{{ __('common.privacy') }}</a>
                <a href="{{ route('terms') }}" class="hidden hover:text-brand-900 sm:inline">{{ __('common.terms') }}</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-primary !px-3 !py-2 text-xs">{{ __('nav.dashboard') }}</a>
                @else
                    <a href="{{ route('login') }}" class="btn-primary !px-3 !py-2 text-xs">{{ __('auth.login_title') }}</a>
                @endauth
                <form method="POST" action="{{ route('locale.update') }}">
                    @csrf
                    <input type="hidden" name="locale" value="{{ app()->getLocale() === 'ar' ? 'en' : 'ar' }}">
                    <button type="submit" class="btn-secondary !px-3 !py-2 text-xs">
                        <x-icon name="globe" class="h-4 w-4" />
                        {{ app()->getLocale() === 'ar' ? __('common.english') : __('common.arabic') }}
                    </button>
                </form>
            </nav>
        </div>
    </header>

    <main id="main-content" class="mx-auto w-full max-w-3xl px-4 py-10 sm:px-6">
        @yield('content')
    </main>

    <footer class="border-t border-grid px-4 py-6 text-xs text-brand-400 sm:px-6">
        <div class="mx-auto flex max-w-5xl flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-col gap-0.5">
                <p>{{ __('common.copyright') }}</p>
                <p><a href="https://fedaamohamed.com" rel="noopener" class="hover:text-brand-700">{{ __('common.credit') }}</a></p>
            </div>
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                <a href="{{ route('about') }}" class="hover:text-brand-700">{{ __('common.about') }}</a>
                <a href="{{ route('privacy') }}" class="hover:text-brand-700">{{ __('common.privacy') }}</a>
                <a href="{{ route('terms') }}" class="hover:text-brand-700">{{ __('common.terms') }}</a>
                <span aria-hidden="true">·</span>
                <span>{{ __('common.version') }}</span>
                <span aria-hidden="true">·</span>
                <span>{{ __('common.demo_public_note') }}</span>
            </div>
        </div>
    </footer>
</body>
</html>
