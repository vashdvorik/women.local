{{--
    Вход в кабинет: сверху «Я уже участница» (форма входа), ниже «Я здесь впервые» (крупная кнопка регистрации). Так тот, кто
    пришёл впервые, видит крупную кнопку сразу под формой, а постоянные участницы не ищут вход под регистрацией. Если вход не
    удался, текст ошибки сам отсылает к кнопке «Зарегистрироваться» (она стоит выше): часто почту вводят те, у кого кабинета ещё нет.
--}}
@extends('account.auth.layout')

@section('title', __('participant.login.title'))
@section('subtitle', __('participant.login.subtitle'))

@section('content')
<h1 class="account-login-heading mb-5 text-center text-2xl">{{ __('participant.login.welcome') }}</h1>

{{-- Уже есть кабинет --}}
<section class="account-login-card p-6" aria-labelledby="login-member-title">
    <h2 id="login-member-title" class="account-login-choice">{{ __('participant.login.member_title') }}</h2>

    <form method="POST" action="{{ route('account.login.submit') }}" class="space-y-4" novalidate>
        @csrf
        <div>
            <label for="email" class="account-login-label">{{ __('participant.login.email') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email', session()->pull('login_email')) }}" required autocomplete="email"
                   class="account-login-input @error('email') is-invalid @enderror">
            @error('email')<p class="account-login-fielderror">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="password" class="account-login-label">{{ __('participant.login.password') }}</label>
            <div class="account-login-pw">
                <input id="password" name="password" type="password" required autocomplete="current-password"
                       class="account-login-input @error('password') is-invalid @enderror">
                <button type="button" class="account-login-pw__toggle" data-pw-toggle="password"
                        data-show="{{ __('participant.login.show_password') }}" data-hide="{{ __('participant.login.hide_password') }}"
                        aria-controls="password">{{ __('participant.login.show_password') }}</button>
            </div>
            @error('password')<p class="account-login-fielderror">{{ $message }}</p>@enderror
        </div>
        <div class="flex items-center justify-between gap-3 text-sm">
            <label class="flex items-center gap-2 account-login-muted">
                <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-gray-300" @checked(old('remember'))>
                {{ __('participant.login.remember') }}
            </label>
            <a href="{{ route('account.password.forgot') }}" class="account-login-link">{{ __('participant.login.forgot') }}</a>
        </div>
        <button type="submit" class="account-login-button account-login-button--big">{{ __('participant.login.submit') }}</button>
    </form>

    @if($googleEnabled)
        <div class="account-login-divider">{{ __('participant.or') }}</div>
        @include('account.auth._google', ['label' => __('participant.google.login')])
    @endif
</section>

{{-- Первый раз здесь: стоит под формой входа, чтобы постоянные участницы не искали вход под регистрацией (после подтверждения почты по ссылке из письма не показываем: кабинет уже создан) --}}
@unless(session('email_verified'))
<section class="account-login-card account-login-card--new mt-4 p-6" aria-labelledby="login-new-title">
    <h2 id="login-new-title" class="account-login-choice">{{ __('participant.login.new_title') }}</h2>
    <p class="account-login-muted account-login-balance mb-4 text-center text-sm">{{ __('participant.login.new_text') }}</p>
    <a href="{{ route('account.register') }}" class="account-login-button account-login-button--big">{{ __('participant.login.new_button') }}</a>
    @if($googleEnabled)
        <div class="account-login-divider">{{ __('participant.or') }}</div>
        @include('account.auth._google', ['label' => __('participant.google.register')])
    @endif
</section>
@endunless

<script>
    // «Показать / Скрыть» пароль: слово понятнее значка глаза
    document.querySelectorAll('[data-pw-toggle]').forEach(function (button) {
        var input = document.getElementById(button.dataset.pwToggle);
        if (!input) return;
        button.addEventListener('click', function () {
            var shown = input.type === 'text';
            input.type = shown ? 'password' : 'text';
            button.textContent = shown ? button.dataset.show : button.dataset.hide;
        });
    });
</script>
@endsection
