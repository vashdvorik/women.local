@extends('account.auth.layout')

@section('title', __('participant.verification.title'))

@section('content')
@include('account.auth._steps', ['step' => 1])

<div class="account-login-card p-7 text-center">
    <span class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full" style="background: var(--login-accent); color: var(--login-focus)" aria-hidden="true">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7l9 6 9-6M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/></svg>
    </span>
    <h1 class="account-login-heading mb-2 text-xl">{{ __('participant.verification.heading') }}</h1>
    <p class="account-login-muted text-sm leading-6">{{ __('participant.verification.text', ['email' => $user->email]) }}</p>
    <p class="account-login-muted mt-2 text-xs">{{ __('participant.verification.spam') }}</p>

    <form method="POST" action="{{ route('account.verification.resend') }}" class="mt-5">
        @csrf
        <button type="submit" class="account-login-button account-login-button--ghost">{{ __('participant.verification.resend') }}</button>
    </form>
</div>

{{-- Ошиблись адресом: исправить его здесь, не заводя новый аккаунт --}}
<details class="account-login-card mt-4 p-5 text-sm" @if($errors->has('email')) open @endif>
    <summary class="account-login-link cursor-pointer text-center">{{ __('participant.verification.wrong_email') }}</summary>
    <form method="POST" action="{{ route('account.verification.change') }}" class="mt-4 space-y-3 text-left">
        @csrf
        <label for="new-email" class="account-login-label">{{ __('participant.verification.change_label') }}</label>
        <input id="new-email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
               class="account-login-input @error('email') is-invalid @enderror">
        @error('email')<p class="account-login-fielderror">{{ $message }}</p>@enderror
        <button type="submit" class="account-login-button">{{ __('participant.verification.change_button') }}</button>
    </form>
</details>

<form method="POST" action="{{ route('account.logout') }}" class="mt-5 text-center text-sm account-login-muted">
    @csrf
    <button type="submit" class="account-login-link">{{ __('participant.verification.logout') }}</button>
</form>
@endsection
