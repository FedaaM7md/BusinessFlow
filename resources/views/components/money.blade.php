{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@props(['minor' => 0, 'class' => ''])
@php
    $formatted = money($minor);
    $parts = preg_split('/\s+/u', trim($formatted), 2);
    $number = $parts[0] ?? $formatted;
    $symbol = $parts[1] ?? '';
@endphp
<span class="inline-flex items-baseline gap-1 whitespace-nowrap {{ $class }}">
    <span class="amount">{{ $number }}</span>
    @if($symbol !== '')
        <span class="text-[0.68em] font-semibold text-brand-500">{{ $symbol }}</span>
    @endif
</span>
