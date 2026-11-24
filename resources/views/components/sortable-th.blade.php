{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@props(['column', 'sort', 'direction'])
@php
    $isActive = $sort === $column;
    $nextDirection = $isActive && $direction === 'asc' ? 'desc' : 'asc';
@endphp
<th {{ $attributes->merge(['scope' => 'col']) }}>
    <a href="{{ request()->fullUrlWithQuery(['sort' => $column, 'direction' => $nextDirection]) }}" class="inline-flex items-center gap-1.5 hover:text-brand-800">
        {{ $slot }}
        @if($isActive)
            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                @if($direction === 'asc')
                    <path fill-rule="evenodd" d="M10 3a.75.75 0 0 1 .7.46l4 9.5a.75.75 0 0 1-1.4.55L10 6.65l-3.3 6.86a.75.75 0 1 1-1.4-.55l4-9.5A.75.75 0 0 1 10 3Z" clip-rule="evenodd"/>
                @else
                    <path fill-rule="evenodd" d="M10 17a.75.75 0 0 1-.7-.46l-4-9.5a.75.75 0 0 1 1.4-.55L10 13.35l3.3-6.86a.75.75 0 1 1 1.4.55l-4 9.5A.75.75 0 0 1 10 17Z" clip-rule="evenodd"/>
                @endif
            </svg>
        @endif
    </a>
</th>
