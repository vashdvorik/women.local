@extends('themes.account.fortuntwo.layout')

@section('title', __('account.opportunities.new_title'))

@section('content')
    <div class="miro-page">
        <header class="miro-page-header">
            <div class="miro-page-header__copy">
                <a href="{{ route('account.opportunities.index') }}" class="miro-button miro-button--text mb-3">← {{ __('account.back') }}</a>
                <p class="miro-eyebrow">{{ __('account.nav.opportunities') }}</p>
                <h1 class="miro-page-title">{{ __('account.opportunities.new_title') }}</h1>
                <p class="miro-page-description">{{ __('account.opportunities.new_subtitle') }}</p>
            </div>
        </header>

        <form method="POST" action="{{ route('account.opportunities.store') }}" class="miro-card miro-form-card">
            @csrf

            <div class="space-y-6">
                <div class="miro-form-field">
                    <label for="type">{{ __('account.opportunities.type') }}</label>
                    <select id="type" name="type">
                        @foreach(['project', 'meeting', 'event'] as $type)
                            <option value="{{ $type }}" @selected(old('type', 'project') === $type)>{{ __('account.types.' . $type) }}</option>
                        @endforeach
                    </select>
                    @error('type')<p class="mt-2 text-xs font-medium text-[#704a0b]">{{ $message }}</p>@enderror
                </div>

                <div class="miro-form-field">
                    <label for="title">{{ __('account.opportunities.title_field') }}</label>
                    <input id="title" name="title" value="{{ old('title') }}" placeholder="{{ __('account.opportunities.title_placeholder') }}" maxlength="200">
                    @error('title')<p class="mt-2 text-xs font-medium text-[#704a0b]">{{ $message }}</p>@enderror
                </div>

                <div class="miro-form-field">
                    <label for="body">{{ __('account.opportunities.description') }}</label>
                    <textarea id="body" name="body" rows="8" maxlength="2000" placeholder="{{ __('account.opportunities.description_placeholder') }}">{{ old('body') }}</textarea>
                    @error('body')<p class="mt-2 text-xs font-medium text-[#704a0b]">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div class="miro-form-field">
                        <label for="event_date">{{ __('account.opportunities.date') }} <span>{{ __('account.optional') }}</span></label>
                        <input id="event_date" type="date" name="event_date" value="{{ old('event_date') }}">
                        @error('event_date')<p class="mt-2 text-xs font-medium text-[#704a0b]">{{ $message }}</p>@enderror
                    </div>
                    <div class="miro-form-field">
                        <label for="location">{{ __('account.opportunities.location') }} <span>{{ __('account.optional') }}</span></label>
                        <input id="location" name="location" value="{{ old('location') }}" placeholder="{{ __('account.opportunities.location_placeholder') }}">
                        @error('location')<p class="mt-2 text-xs font-medium text-[#704a0b]">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="miro-form-field">
                    <label for="contact_url">{{ __('account.opportunities.contact_url') }} <span>{{ __('account.optional') }}</span></label>
                    <input id="contact_url" type="url" name="contact_url" value="{{ old('contact_url') }}" placeholder="{{ __('account.opportunities.contact_placeholder') }}">
                    @error('contact_url')<p class="mt-2 text-xs font-medium text-[#704a0b]">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-8 flex flex-col-reverse gap-3 border-t border-[#e7e7e9] pt-6 sm:flex-row sm:justify-end">
                <a href="{{ route('account.opportunities.index') }}" class="miro-button miro-button--outline">{{ __('account.back') }}</a>
                <button class="miro-button miro-button--dark">{{ __('account.opportunities.submit') }}</button>
            </div>
        </form>
    </div>
@endsection

