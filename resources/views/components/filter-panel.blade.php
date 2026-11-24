{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@props(['class' => 'mb-5'])
<div {{ $attributes->merge(['class' => 'relative '.$class]) }} x-data="{ open: false }">
    <button type="button" class="btn-secondary flex w-full items-center justify-between md:hidden" @click="open = true" :aria-expanded="open">
        <span class="inline-flex items-center gap-2"><x-icon name="filter" class="h-4 w-4" /> {{ __('common.filter') }}</span>
        <x-icon name="chevron-down" class="h-4 w-4" />
    </button>

    <div x-show="open" x-cloak @click="open = false" class="fixed inset-0 z-40 bg-brand-950/40 md:hidden" aria-hidden="true"></div>

    <div
        class="fixed inset-x-0 bottom-0 z-50 max-h-[85vh] overflow-y-auto rounded-t-2xl border-t border-grid bg-white p-4 shadow-2xl md:static md:z-auto md:max-h-none md:overflow-visible md:rounded-lg md:border md:p-4 md:shadow-[0_1px_2px_rgba(20,33,61,0.04)]"
        :class="open ? 'block' : 'hidden md:block'"
        :role="open ? 'dialog' : null"
        :aria-modal="open ? 'true' : 'false'"
        aria-label="{{ __('common.filter') }}"
    >
        <div class="mb-3 flex items-center justify-between md:hidden">
            <p class="text-sm font-bold text-brand-900">{{ __('common.filter') }}</p>
            <button type="button" class="icon-btn" @click="open = false" aria-label="{{ __('common.close') }}"><x-icon name="close" class="h-4 w-4" /></button>
        </div>
        {{ $slot }}
    </div>
</div>
