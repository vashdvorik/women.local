@extends('account.auth.layout')

@section('title', __('participant.register.title'))
@section('subtitle', __('participant.register.subtitle'))

@section('content')
<div class="account-login-card p-7">
    <h1 class="account-login-heading mb-5 text-center text-xl">{{ __('participant.register.heading') }}</h1>

    <form method="POST" action="{{ route('account.register.submit') }}" class="space-y-4" novalidate>
        @csrf
        <div>
            <label for="email" class="account-login-label">{{ __('participant.register.email') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                   class="account-login-input @error('email') is-invalid @enderror">
            @error('email')<p class="account-login-fielderror">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password" class="account-login-label">{{ __('participant.register.password') }}</label>
            <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password"
                   class="account-login-input @error('password') is-invalid @enderror">
            <p class="account-login-hint">{{ __('participant.register.password_hint') }}</p>
            @error('password')<p class="account-login-fielderror">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password_confirmation" class="account-login-label">{{ __('participant.register.password_confirmation') }}</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password"
                   class="account-login-input">
        </div>
        <div>
            <label class="flex items-start gap-2 text-sm account-login-muted">
                <input type="checkbox" name="consent" value="1" class="mt-1 h-4 w-4 shrink-0 rounded border-gray-300" @checked(old('consent')) required>
                <span>{{ __('participant.register.consent') }}
                    <a href="{{ route('about.regulations') }}" target="_blank" rel="noopener" class="account-login-link">{{ __('participant.register.consent_link') }}</a></span>
            </label>
            @error('consent')<p class="account-login-fielderror">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="account-login-button">{{ __('participant.register.submit') }}</button>
    </form>

    @if($googleEnabled)
        <div class="account-login-divider">{{ __('participant.or') }}</div>
        @include('account.auth._google', ['label' => __('participant.google.register')])
    @endif
</div>

<p class="mt-5 text-center text-sm account-login-muted">
    {{ __('participant.register.have_account') }}
    <a href="{{ route('account.login') }}" class="account-login-link">{{ __('participant.register.login_link') }}</a>
</p>
@endsection
