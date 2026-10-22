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

    <form action="{{ route('account.profile.update') }}" method="POST" enctype="multipart/form-data" class="miro-card miro-form-card">
        @csrf
        <div class="space-y-6">
            {{-- Фото профиля: показывается текущее, выбранный файл виден сразу (до сохранения). Режется по центру в квадрат на сервере. --}}
            <div class="miro-form-field fortun-photo-field">
                <label for="photo">{{ __('account.profile_edit.photo_label') }}</label>
                <div class="fortun-photo-field__row">
                    <span class="fortun-photo-field__preview">
                        <img id="photo-preview" src="{{ $accountUser->avatar_path ? Storage::url($accountUser->avatar_path) : '' }}" alt="" @unless($accountUser->avatar_path) hidden @endunless>
                        <span id="photo-placeholder" @if($accountUser->avatar_path) hidden @endif>{{ mb_strtoupper(mb_substr($accountUser->full_name ?: $accountUser->telegram_username ?: '?', 0, 1)) }}</span>
                    </span>
                    <div class="fortun-photo-field__controls">
                        <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp">
                        @if($accountUser->avatar_path)
                            <label class="fortun-photo-field__remove"><input type="checkbox" name="remove_photo" value="1"> <span>{{ __('account.profile_edit.photo_remove') }}</span></label>
                        @endif
                    </div>
                </div>
                <small>{{ __('account.profile_edit.photo_help') }}</small>
                @error('photo')<p class="mt-2 text-xs font-medium text-[#8C4842]">{{ $message }}</p>@enderror
            </div>
            <div class="miro-form-field">
                <label for="full_name">{{ __('account.profile.full_name') }} <span>{{ __('account.profile_edit.full_name_help') }}</span></label>
                <input id="full_name" name="full_name" value="{{ old('full_name', $accountUser->full_name) }}" maxlength="120" autocomplete="name">
                @error('full_name')<p class="mt-2 text-xs font-medium text-[#8C4842]">{{ $message }}</p>@enderror
            </div>
            <div class="miro-form-field">
                <label for="description">{{ __('account.profile_edit.description_label') }} <span>1000</span></label>
                <textarea id="description" name="description" maxlength="1000" placeholder="{{ __('account.profile_edit.description_placeholder') }}">{{ old('description', $accountUser->description) }}</textarea>
                <small>{{ __('account.profile_edit.description_help') }}</small>
                @error('description')<p class="mt-2 text-xs font-medium text-[#8C4842]">{{ $message }}</p>@enderror
            </div>
            <div class="miro-form-field">
                <label for="expectation">{{ __('account.profile_edit.expectation_label') }} <span>1000</span></label>
                <textarea id="expectation" name="expectation" maxlength="1000" placeholder="{{ __('account.profile_edit.expectation_placeholder') }}">{{ old('expectation', $accountUser->expectation) }}</textarea>
                <small>{{ __('account.profile_edit.expectation_help') }}</small>
                @error('expectation')<p class="mt-2 text-xs font-medium text-[#8C4842]">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="mt-8 flex flex-col-reverse gap-3 border-t border-[#E9DCD5] pt-6 sm:flex-row sm:justify-end">
            <a href="{{ route('account.profile') }}" class="miro-button miro-button--outline">{{ __('account.back') }}</a>
            <button class="miro-button miro-button--dark">{{ __('account.profile_edit.save') }}</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var input = document.getElementById('photo');
        if (!input) return;
        input.addEventListener('change', function () {
            var file = input.files && input.files[0];
            if (!file) return;
            var img = document.getElementById('photo-preview');
            var placeholder = document.getElementById('photo-placeholder');
            img.src = URL.createObjectURL(file);
            img.hidden = false;
            if (placeholder) placeholder.hidden = true;
        });
    }());
</script>
@endpush

