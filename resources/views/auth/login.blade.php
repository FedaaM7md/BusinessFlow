{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.guest')

@section('title', __('auth.login_title'))

@section('content')
    @php
        $demoRoles = $demoRoles ?? collect();
        $roleIcons = ['admin' => 'roles', 'accountant' => 'reports', 'sales' => 'invoices', 'warehouse' => 'inventory'];
    @endphp

    <div class="mb-7">
        <h1 class="text-2xl font-bold tracking-tight text-brand-900 sm:text-3xl">{{ __('auth.welcome_back') }}</h1>
        <p class="mt-2 text-sm leading-6 text-brand-500">{{ __('auth.login_subtitle') }}</p>
    </div>

    <div class="flex flex-col">
        <div class="order-2 sm:order-1">
            <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="label">{{ __('fields.email') }}</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="input @error('email') !border-negative-600 @enderror" placeholder="{{ __('auth.email_placeholder') }}" dir="ltr">
                    @error('email') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div>
                    <div class="mb-1.5 flex items-center justify-between gap-3">
                        <label for="password" class="block text-sm font-semibold text-brand-800">{{ __('fields.password') }}</label>
                        <a href="{{ route('password.request') }}" class="text-xs font-semibold text-accent-700 hover:underline">{{ __('auth.forgot_password') }}</a>
                    </div>
                    <input id="password" name="password" type="password" required autocomplete="current-password" class="input @error('password') !border-negative-600 @enderror" placeholder="••••••••" dir="ltr">
                    @error('password') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <label class="flex items-center gap-2 text-sm text-brand-600">
                    <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-grid text-brand-800 focus:ring-accent-500">
                    {{ __('auth.remember_me') }}
                </label>

                <button type="submit" class="btn-primary w-full !py-3">
                    {{ __('auth.sign_in') }}
                    <x-icon name="arrow-start" class="h-4 w-4 rtl:rotate-180" />
                </button>
            </form>
        </div>

        @if(demo_enabled() && $demoRoles->isNotEmpty())
            <div class="order-1 mb-6 sm:order-2 sm:mb-0 sm:mt-7">
                <div class="relative mb-5 hidden sm:block">
                    <div class="absolute inset-0 flex items-center" aria-hidden="true"><div class="w-full border-t border-grid"></div></div>
                    <div class="relative flex justify-center"><span class="bg-paper px-3 text-xs font-medium uppercase tracking-wide text-brand-400">{{ __('common.or') }}</span></div>
                </div>

                <div class="rounded-lg border border-accent-200 bg-accent-50/70 p-4">
                    <div class="flex items-start gap-2.5">
                        <x-icon name="info" class="mt-0.5 h-4 w-4 shrink-0 text-accent-600" />
                        <div>
                            <p class="text-sm font-bold text-brand-900">{{ __('auth.demo_try_title') }}</p>
                            <p class="mt-1 text-xs leading-5 text-brand-500">{{ __('auth.demo_try_note') }}</p>
                        </div>
                    </div>

                    <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-brand-400">{{ __('auth.demo_pick_role') }}</p>
                    <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2">
                        @foreach($demoRoles as $role)
                            <form method="POST" action="{{ route('demo.login') }}">
                                @csrf
                                <input type="hidden" name="role" value="{{ $role->name }}">
                                <button type="submit" class="btn-secondary w-full !justify-start !px-3 !py-2 text-xs">
                                    <x-icon :name="$roleIcons[$role->name] ?? 'users'" class="h-4 w-4 shrink-0 text-accent-600" />
                                    <span class="min-w-0 truncate">{{ $role->displayName() }}</span>
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    @error('role') <p class="mt-3 error-text">{{ $message }}</p> @enderror

    <p class="mt-6 text-center text-[11px] leading-5 text-brand-400">{{ __('auth.demo_access_note') }}</p>
@endsection
