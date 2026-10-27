@extends('account.auth.layout')

@section('title', __('participant.login.title'))
@section('subtitle', __('participant.login.subtitle'))

@section('content')
<div class="account-login-card p-7">
    <h1 class="account-login-heading mb-5 text-center text-xl">{{ __('participant.login.heading') }}</h1>

    <form method="POST" action="{{ route('account.login.submit') }}" class="space-y-4" novalidate>
        @csrf
        <div>
            <label for="email" class="account-login-label">{{ __('participant.login.email') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                   class="account-login-input @error('email') is-invalid @enderror">
            @error('email')<p class="account-login-fielderror">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password" class="account-login-label">{{ __('participant.login.password') }}</label>
            <input id="password" name="password" type="password" required autocomplete="current-password"
                   class="account-login-input @error('password') is-invalid @enderror">
            @error('password')<p class="account-login-fielderror">{{ $message }}</p>@enderror
        </div>
        <div class="flex items-center justify-between gap-3 text-sm">
            <label class="flex items-center gap-2 account-login-muted">
                <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-gray-300" @checked(old('remember'))>
                {{ __('participant.login.remember') }}
            </label>
            <a href="{{ route('account.password.forgot') }}" class="account-login-link">{{ __('participant.login.forgot') }}</a>
        </div>
        <button type="submit" class="account-login-button">{{ __('participant.login.submit') }}</button>
    </form>

    @if($googleEnabled)
        <div class="account-login-divider">{{ __('participant.or') }}</div>
        @include('account.auth._google', ['label' => __('participant.google.login')])
    @endif
</div>

<p class="mt-5 text-center text-sm account-login-muted">
    {{ __('participant.login.no_account') }}
    <a href="{{ route('account.register') }}" class="account-login-link">{{ __('participant.login.register_link') }}</a>
</p>
@endsection
