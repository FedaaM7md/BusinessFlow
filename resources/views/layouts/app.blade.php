{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@php($user = auth()->user())
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ __('common.app_description') }}">
    @include('partials.meta', ['defaultTitle' => __('nav.dashboard')])
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-paper text-brand-900 antialiased" x-data="appShell" @keydown.escape.window="hide" @keydown.tab="trap($event)">
<a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:start-4 focus:top-4 focus:z-[100] focus:rounded-md focus:bg-brand-800 focus:px-4 focus:py-2 focus:text-white">{{ __('common.skip_to_content') }}</a>

<div class="lg:ps-72">
    @include('partials.sidebar')

    <div x-show="open" x-cloak @click="hide" class="fixed inset-0 z-40 bg-brand-950/40 lg:hidden" aria-hidden="true"></div>

    <header class="sticky top-0 z-30 border-b border-grid bg-white/95 backdrop-blur">
        <div class="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
            <button type="button" class="icon-btn lg:hidden" @click="show" :aria-expanded="open" aria-controls="app-sidebar" aria-label="{{ __('nav.open_menu') }}">
                <x-icon name="menu" class="h-5 w-5" />
            </button>

            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-bold text-brand-900">@yield('title', __('nav.dashboard'))</p>
                <p class="hidden truncate text-xs text-brand-400 sm:block">{{ now()->locale(app()->getLocale())->translatedFormat(__('common.date_format')) }}</p>
            </div>

            <form method="POST" action="{{ route('locale.update') }}">
                @csrf
                <input type="hidden" name="locale" value="{{ app()->getLocale() === 'ar' ? 'en' : 'ar' }}">
                <button type="submit" class="btn-secondary !px-3 !py-2 text-xs" title="{{ __('common.switch_language') }}">
                    <x-icon name="globe" class="h-4 w-4" />
                    {{ app()->getLocale() === 'ar' ? __('common.english') : __('common.arabic') }}
                </button>
            </form>

            <div class="relative" x-data="{ open: false }">
                <button type="button" @click="open = !open" class="flex items-center gap-2 rounded-md p-1.5 transition hover:bg-brand-50" :aria-expanded="open">
                    <span class="flex h-9 w-9 items-center justify-center rounded-md bg-brand-800 text-sm font-bold text-white">{{ mb_substr($user->name, 0, 1) }}</span>
                    <span class="hidden text-start sm:block">
                        <span class="block max-w-36 truncate text-xs font-bold text-brand-900">{{ $user->name }}</span>
                        <span class="block text-[11px] text-brand-400">{{ $user->role?->displayName() ?? __('common.no_role') }}</span>
                    </span>
                    <x-icon name="chevron-down" class="hidden h-4 w-4 text-brand-400 sm:block" />
                </button>
                <div x-show="open" x-transition @click.outside="open = false" class="absolute end-0 z-50 mt-2 w-56 overflow-hidden rounded-md border border-grid bg-white shadow-xl">
                    <div class="border-b border-grid px-4 py-3">
                        <p class="truncate text-sm font-bold text-brand-900">{{ $user->name }}</p>
                        <p class="truncate text-xs text-brand-500">{{ $user->email }}</p>
                        @if($user->is_demo)
                            <p class="mt-1 inline-flex items-center gap-1 text-[11px] font-semibold text-accent-600"><x-icon name="info" class="h-3.5 w-3.5" /> {{ __('common.demo') }}</p>
                        @endif
                    </div>
                    @can('settings.manage')
                        <a href="{{ route('settings.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-brand-700 hover:bg-brand-50">
                            <x-icon name="settings" class="h-4 w-4" /> {{ __('nav.settings') }}
                        </a>
                    @endcan
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-2 px-4 py-2.5 text-start text-sm text-negative-600 hover:bg-negative-50">
                            <x-icon name="logout" class="h-4 w-4" /> {{ __('common.logout') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    @if(demo_enabled())
        <div class="no-print flex items-center gap-2 border-b border-accent-200 bg-accent-50 px-4 py-2 text-xs font-medium text-accent-700 sm:px-6 lg:px-8" role="status">
            <x-icon name="info" class="h-4 w-4 shrink-0" />
            <span>{{ __('common.demo_mode_banner') }}</span>
            <span class="hidden text-accent-600 sm:inline">— {{ __('common.demo_reset_note') }}</span>
        </div>
    @endif

    <main id="main-content" class="mx-auto w-full max-w-[1500px] px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
        @include('partials.flash')
        @yield('content')
    </main>

    <footer class="mx-auto w-full max-w-[1500px] border-t border-grid px-4 py-5 text-xs text-brand-400 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
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
</div>
@stack('scripts')
</body>
</html>
