<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\RegisterRequest;
use App\Models\BotUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

/** Вход и регистрация участниц по почте и паролю (guard «participant»). Вход через Google — GoogleController. */
class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::guard('participant')->check()) {
            return redirect()->route('account.index');
        }

        return view('account.auth.login', ['googleEnabled' => GoogleController::enabled()]);
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => __('participant.validation.email_required'),
            'email.email' => __('participant.validation.email_invalid'),
            'password.required' => __('participant.validation.password_required'),
        ]);

        $email = Str::lower(trim($data['email']));
        $throttleKey = $email.'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return back()->withInput($request->only('email'))
                ->with('error', __('participant.login.throttle', ['seconds' => RateLimiter::availableIn($throttleKey)]));
        }

        $guard = Auth::guard('participant');

        if (! $guard->attempt(['email' => $email, 'password' => $data['password']], $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);

            return back()->withInput($request->only('email'))->with('error', __('participant.login.failed'));
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        /** @var BotUser $user */
        $user = $guard->user();

        if ($user->isRejected()) {
            $guard->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('account.login')->with('error', __('participant.login.access_closed'));
        }

        $user->markCabinetOpened();

        return redirect()->intended(route('account.index'));
    }

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::guard('participant')->check()) {
            return redirect()->route('account.index');
        }

        return view('account.auth.register', ['googleEnabled' => GoogleController::enabled()]);
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = BotUser::create([
            'email' => $request->validated('email'),
            'locale' => in_array(app()->getLocale(), ['ru', 'en', 'ro'], true) ? app()->getLocale() : 'ru',
        ]);
        $user->forceFill(['password' => $request->validated('password')])->save();

        Auth::guard('participant')->login($user);
        $request->session()->regenerate();

        try {
            $user->sendEmailVerification();
        } catch (\Throwable $e) {
            // Почта сайта может быть ещё не настроена: участница увидит страницу с кнопкой «Отправить ещё раз».
            Log::warning('Не удалось отправить письмо подтверждения почты', ['user' => $user->id, 'error' => $e->getMessage()]);
        }

        return redirect()->route('account.verification.notice')->with('success', __('participant.verification.sent'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('participant')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('account.login');
    }
}
