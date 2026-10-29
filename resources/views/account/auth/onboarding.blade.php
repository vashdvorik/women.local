@extends('account.auth.layout', ['wide' => true])

@section('title', __('participant.onboarding.title'))

@section('content')
@include('account.auth._steps', ['step' => 2])

<div class="account-login-card p-7">
    <h1 class="account-login-heading mb-2 text-center text-xl">{{ __('participant.onboarding.heading') }}</h1>

    <form method="POST" action="{{ route('account.onboarding.store') }}" class="space-y-4" novalidate>
        @csrf
        <div>
            <label for="full_name" class="account-login-label">{{ __('participant.onboarding.full_name') }}</label>
            <input id="full_name" name="full_name" type="text" value="{{ old('full_name', $user->full_name) }}" required maxlength="120" autofocus autocomplete="name"
                   class="account-login-input @error('full_name') is-invalid @enderror">
            @error('full_name')<p class="account-login-fielderror">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="phone" class="account-login-label">{{ __('participant.onboarding.phone') }}</label>
            <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" required maxlength="32" inputmode="tel" autocomplete="tel"
                   class="account-login-input @error('phone') is-invalid @enderror">
            <p class="account-login-hint">{{ __('participant.onboarding.phone_hint') }}</p>
            @error('phone')<p class="account-login-fielderror">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="description" class="account-login-label">{{ __('participant.onboarding.description') }}</label>
            <textarea id="description" name="description" rows="4" required maxlength="1000"
                      class="account-login-input @error('description') is-invalid @enderror">{{ old('description', $user->description) }}</textarea>
            <p class="account-login-hint">{{ __('participant.onboarding.description_hint') }}</p>
            @error('description')<p class="account-login-fielderror">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="expectation" class="account-login-label">{{ __('participant.onboarding.expectation') }}</label>
            <textarea id="expectation" name="expectation" rows="4" required maxlength="1000"
                      class="account-login-input @error('expectation') is-invalid @enderror">{{ old('expectation', $user->expectation) }}</textarea>
            <p class="account-login-hint">{{ __('participant.onboarding.expectation_hint') }}</p>
            @error('expectation')<p class="account-login-fielderror">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="account-login-button">{{ __('participant.onboarding.submit') }}</button>
    </form>
</div>

<form method="POST" action="{{ route('account.logout') }}" class="mt-5 text-center text-sm">
    @csrf
    <button type="submit" class="account-login-link">{{ __('account.logout') }}</button>
</form>
@endsection
