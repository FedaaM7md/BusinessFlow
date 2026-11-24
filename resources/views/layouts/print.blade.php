{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">
    <title>@yield('title') · {{ company()->displayName() }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-paper py-6 text-brand-900 print:bg-white print:py-0">
    <div class="no-print mx-auto mb-4 flex max-w-4xl items-center justify-between gap-3 px-4">
        <a href="{{ url()->previous() }}" class="btn-secondary"><x-icon name="arrow-start" class="h-4 w-4 rtl:rotate-180" /> {{ __('common.back') }}</a>
        <button type="button" data-print class="btn-primary"><x-icon name="print" class="h-4 w-4" /> {{ __('common.print') }}</button>
    </div>
    <main class="print-sheet mx-auto max-w-4xl bg-white p-8 shadow-xl sm:p-12 print:shadow-none">
        @yield('content')
    </main>
</body>
</html>
