{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@props(['href', 'active' => false, 'icon' => 'circle'])
<a href="{{ $href }}" @class(['nav-link', 'nav-link-active' => $active]) @if($active) aria-current="page" @endif>
    <x-icon :name="$icon" class="h-5 w-5 shrink-0 {{ $active ? 'text-white' : 'text-brand-400' }}" />
    <span class="truncate">{{ $slot }}</span>
</a>
