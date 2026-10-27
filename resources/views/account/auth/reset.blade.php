@extends('account.auth.layout')

@section('title', __('participant.password.reset_title'))

@section('content')
<div class="account-login-card p-7">
    <h1 class="account-login-heading mb-5 text-center text-xl">{{ __('participant.password.reset_heading') }}</h1>

    <form method="POST" action="{{ route('account.password.update') }}" class="space-y-4" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div>
            <label for="email" class="account-login-label">{{ __('participant.login.email') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required autocomplete="email"
                   class="account-login-input @error('email') is-invalid @enderror">
            @error('email')<p class="account-login-fielderror">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password" class="account-login-label">{{ __('participant.register.password') }}</label>
            <input id="password" name="password" type="password" required minlength="8" autofocus autocomplete="new-password"
                   class="account-login-input @error('password') is-invalid @enderror">
            <p class="account-login-hint">{{ __('participant.register.password_hint') }}</p>
            @error('password')<p class="account-login-fielderror">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password_confirmation" class="account-login-label">{{ __('participant.register.password_confirmation') }}</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password"
                   class="account-login-input">
        </div>
        <button type="submit" class="account-login-button">{{ __('participant.password.reset_submit') }}</button>
    </form>
</div>
@endsection
