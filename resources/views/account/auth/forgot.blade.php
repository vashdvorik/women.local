@extends('account.auth.layout')

@section('title', __('participant.password.forgot_title'))

@section('content')
<div class="account-login-card p-7">
    <h1 class="account-login-heading mb-2 text-center text-xl">{{ __('participant.password.forgot_heading') }}</h1>
    <p class="account-login-muted mb-5 text-center text-sm leading-6">{{ __('participant.password.forgot_text') }}</p>

    <form method="POST" action="{{ route('account.password.email') }}" class="space-y-4" novalidate>
        @csrf
        <div>
            <label for="email" class="account-login-label">{{ __('participant.login.email') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                   class="account-login-input @error('email') is-invalid @enderror">
            @error('email')<p class="account-login-fielderror">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="account-login-button">{{ __('participant.password.forgot_submit') }}</button>
    </form>
</div>

<p class="mt-5 text-center text-sm"><a href="{{ route('account.login') }}" class="account-login-link">{{ __('participant.password.back_to_login') }}</a></p>
@endsection
