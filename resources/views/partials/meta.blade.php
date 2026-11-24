{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@php($metaTitle = trim($__env->yieldContent('title')) ?: ($defaultTitle ?? __('common.app_name')))
@php($metaDescription = trim($__env->yieldContent('meta_description')) ?: __('common.app_description'))
<title>{{ $metaTitle }} — {{ __('common.app_name') }}</title>
<meta name="description" content="{{ $metaDescription }}">
<meta property="og:title" content="{{ $metaTitle }} — {{ __('common.app_name') }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:site_name" content="{{ __('common.app_name') }}">
<meta property="og:locale" content="{{ app()->getLocale() === 'ar' ? 'ar_EG' : 'en_US' }}">
<meta property="og:image" content="{{ asset('images/og.png') }}">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">
