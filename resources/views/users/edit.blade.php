{{-- BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. --}}
@extends('layouts.app')
@section('title', __('users.edit'))
@section('content')
    <x-page-header :title="__('users.edit')" :subtitle="$user->name"><x-slot:actions><a href="{{ route('users.index') }}" class="btn-secondary">{{ __('common.back') }}</a></x-slot:actions></x-page-header>
    <form method="POST" action="{{ route('users.update', $user) }}">
        @csrf @method('PUT')
        <div class="form-section">
            @include('users.form', ['user' => $user])
            <div class="mt-6 flex items-center justify-between gap-2 border-t border-slate-100 pt-5">
                @if($user->id !== auth()->id())
                    <button type="button" class="btn-ghost !text-rose-600" data-submit-form="delete-user" data-confirm="{{ __('common.confirm_delete_text') }}">{{ __('common.delete') }}</button>
                @else <span></span> @endif
                <div class="flex gap-2"><a href="{{ route('users.index') }}" class="btn-secondary">{{ __('common.cancel') }}</a><button class="btn-primary">{{ __('common.save_changes') }}</button></div>
            </div>
        </div>
    </form>
    @if($user->id !== auth()->id())<form id="delete-user" method="POST" action="{{ route('users.destroy', $user) }}" class="hidden">@csrf @method('DELETE')</form>@endif
@endsection
