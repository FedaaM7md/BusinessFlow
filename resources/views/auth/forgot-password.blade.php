{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.guest')

@section('title', __('auth.forgot_password'))

@section('content')
    <div class="mb-7">
        <h1 class="text-2xl font-bold tracking-tight text-brand-900 sm:text-3xl">{{ __('auth.forgot_password') }}</h1>
        <p class="mt-2 text-sm leading-6 text-brand-500">{{ __('auth.forgot_subtitle') }}</p>
    </div>

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="label">{{ __('fields.email') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="input @error('email') !border-negative-600 @enderror" placeholder="{{ __('auth.email_placeholder') }}" dir="ltr">
            @error('email') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="btn-primary w-full !py-3">{{ __('auth.send_reset_link') }}</button>
    </form>

    <a href="{{ route('login') }}" class="mt-6 inline-flex items-center gap-1.5 text-sm font-semibold text-accent-700 hover:underline">
        <x-icon name="arrow-start" class="h-4 w-4 rtl:rotate-180" /> {{ __('auth.back_to_login') }}
    </a>
@endsection
