{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@props(['label', 'value' => null, 'amountMinor' => null, 'icon' => 'reports', 'tone' => 'brand', 'hint' => null, 'href' => null])
@php
    $tones = [
        'brand' => 'bg-brand-50 text-brand-800',
        'accent' => 'bg-accent-50 text-accent-700',
        'amber' => 'bg-accent-50 text-accent-700',
        'rose' => 'bg-negative-50 text-negative-700',
        'slate' => 'bg-brand-50 text-brand-600',
    ];
@endphp
<div {{ $attributes->merge(['class' => 'stat-card']) }}>
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="kpi-label">{{ $label }}</p>
            <p class="kpi-value">
                @if($amountMinor !== null)
                    <x-money :minor="$amountMinor" />
                @else
                    {{ $value }}
                @endif
            </p>
            @if($hint)<p class="mt-1 text-xs text-slate-400">{{ $hint }}</p>@endif
        </div>
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $tones[$tone] ?? $tones['brand'] }}">
            <x-icon :name="$icon" class="h-5 w-5" />
        </div>
    </div>
    @if($href)
        <a href="{{ $href }}" class="absolute inset-0 rounded-2xl" aria-label="{{ $label }}"></a>
    @endif
</div>
