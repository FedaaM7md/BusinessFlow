{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@props(['paginator'])
@if($paginator->total() > 0)
    <div class="flex flex-col gap-3 border-t border-grid px-4 py-4 text-sm text-brand-500 sm:flex-row sm:items-center sm:justify-between">
        <p>{{ __('common.showing_results', ['from' => $paginator->firstItem(), 'to' => $paginator->lastItem(), 'total' => $paginator->total()]) }}</p>
        @if($paginator->hasPages())
            <nav class="flex items-center gap-1.5" aria-label="{{ __('common.pagination') }}">
                @if($paginator->onFirstPage())
                    <span class="icon-btn cursor-not-allowed opacity-40"><x-icon name="chevron-start" class="h-4 w-4 rtl:rotate-180" /></span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="icon-btn" rel="prev" aria-label="{{ __('common.previous') }}"><x-icon name="chevron-start" class="h-4 w-4 rtl:rotate-180" /></a>
                @endif
                <span class="px-2 font-medium text-brand-700">{{ __('common.page_of', ['current' => $paginator->currentPage(), 'last' => $paginator->lastPage()]) }}</span>
                @if($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="icon-btn" rel="next" aria-label="{{ __('common.next') }}"><x-icon name="chevron-end" class="h-4 w-4 rtl:rotate-180" /></a>
                @else
                    <span class="icon-btn cursor-not-allowed opacity-40"><x-icon name="chevron-end" class="h-4 w-4 rtl:rotate-180" /></span>
                @endif
            </nav>
        @endif
    </div>
@endif
