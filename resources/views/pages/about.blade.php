{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.public')

@section('title', __('pages.about.title'))
@section('meta_description', __('pages.meta.about'))

@section('content')
    <article class="space-y-9">
        <header>
            <p class="text-xs font-semibold text-accent-700">{{ __('common.version') }}</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-brand-900">{{ __('pages.about.title') }}</h1>
            <p class="mt-3 text-base leading-8 text-brand-600">{{ __('pages.about.intro') }}</p>
        </header>

        <section>
            <h2 class="text-xl font-bold text-brand-900">{{ __('pages.about.story_title') }}</h2>
            <p class="mt-2 text-sm leading-7 text-brand-600">{{ __('pages.about.story') }}</p>
        </section>

        <section>
            <h2 class="text-xl font-bold text-brand-900">{{ __('pages.about.values_title') }}</h2>
            <ul class="mt-3 space-y-2 text-sm leading-7 text-brand-600">
                @foreach(__('pages.about.values') as $value)
                    <li class="flex gap-2">
                        <x-icon name="check" class="mt-1 h-4 w-4 shrink-0 text-positive-600" />
                        <span>{{ $value }}</span>
                    </li>
                @endforeach
            </ul>
        </section>

        <section id="contact">
            <h2 class="text-xl font-bold text-brand-900">{{ __('pages.about.contact_title') }}</h2>
            <p class="mt-2 text-sm leading-7 text-brand-600">{{ __('pages.about.contact') }}</p>
        </section>

        <section>
            <h2 class="text-xl font-bold text-brand-900">{{ __('pages.about.faq_title') }}</h2>
            <div class="mt-3 space-y-3">
                @foreach(__('pages.about.faq') as $item)
                    <details class="rounded-lg border border-grid bg-white p-4">
                        <summary class="cursor-pointer text-sm font-semibold text-brand-900">{{ $item['q'] }}</summary>
                        <p class="mt-2 text-sm leading-7 text-brand-600">{{ $item['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </section>

        <section id="changelog">
            <h2 class="text-xl font-bold text-brand-900">{{ __('pages.about.changelog_title') }}</h2>
            <p class="mt-2 text-sm leading-7 text-brand-600">{{ __('pages.about.changelog') }}</p>
        </section>
    </article>
@endsection
