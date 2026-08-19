@extends('themes.account.fortuntwo.layout')
@section('title', __('account.profile_edit.title'))

@section('content')
<div class="miro-page">
    <header class="miro-page-header">
        <div class="miro-page-header__copy">
            <a href="{{ route('account.profile') }}" class="miro-button miro-button--text mb-3">← {{ __('account.back') }}</a>
            <p class="miro-eyebrow">{{ __('account.nav.profile') }}</p>
            <h1 class="miro-page-title">{{ __('account.profile_edit.title') }}</h1>
            <p class="miro-page-description">{{ __('account.profile_edit.subtitle') }}</p>
        </div>
    </header>

    <form action="{{ route('account.profile.update') }}" method="POST" class="miro-card miro-form-card">
        @csrf
        <div class="space-y-6">
            <div class="miro-form-field">
                <label for="full_name">{{ __('account.profile.full_name') }} <span>{{ __('account.profile_edit.full_name_help') }}</span></label>
                <input id="full_name" name="full_name" value="{{ old('full_name', $accountUser->full_name) }}" maxlength="120" autocomplete="name">
                @error('full_name')<p class="mt-2 text-xs font-medium text-[#704a0b]">{{ $message }}</p>@enderror
            </div>
            <div class="miro-form-field">
                <label for="description">{{ __('account.profile_edit.description_label') }} <span>1000</span></label>
                <textarea id="description" name="description" maxlength="1000" placeholder="{{ __('account.profile_edit.description_placeholder') }}">{{ old('description', $accountUser->description) }}</textarea>
                <small>{{ __('account.profile_edit.description_help') }}</small>
                @error('description')<p class="mt-2 text-xs font-medium text-[#704a0b]">{{ $message }}</p>@enderror
            </div>
            <div class="miro-form-field">
                <label for="expectation">{{ __('account.profile_edit.expectation_label') }} <span>1000</span></label>
                <textarea id="expectation" name="expectation" maxlength="1000" placeholder="{{ __('account.profile_edit.expectation_placeholder') }}">{{ old('expectation', $accountUser->expectation) }}</textarea>
                <small>{{ __('account.profile_edit.expectation_help') }}</small>
                @error('expectation')<p class="mt-2 text-xs font-medium text-[#704a0b]">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="mt-8 flex flex-col-reverse gap-3 border-t border-[#e7e7e9] pt-6 sm:flex-row sm:justify-end">
            <a href="{{ route('account.profile') }}" class="miro-button miro-button--outline">{{ __('account.back') }}</a>
            <button class="miro-button miro-button--dark">{{ __('account.profile_edit.save') }}</button>
        </div>
    </form>
</div>
@endsection

