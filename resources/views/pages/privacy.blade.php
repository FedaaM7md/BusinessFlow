{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.public')

@section('title', __('pages.privacy.title'))
@section('meta_description', __('pages.meta.privacy'))

@section('content')
    <article class="space-y-8">
        <header>
            <h1 class="text-3xl font-bold tracking-tight text-brand-900">{{ __('pages.privacy.title') }}</h1>
            <p class="mt-2 text-xs text-brand-400">{{ __('pages.privacy.updated') }}</p>
            <p class="mt-3 text-base leading-8 text-brand-600">{{ __('pages.privacy.intro') }}</p>
        </header>

        @foreach(__('pages.privacy.sections') as $section)
            <section>
                <h2 class="text-lg font-bold text-brand-900">{{ $section['title'] }}</h2>
                <p class="mt-2 text-sm leading-7 text-brand-600">{{ $section['body'] }}</p>
            </section>
        @endforeach
    </article>
@endsection
