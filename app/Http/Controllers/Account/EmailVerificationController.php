<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\ChangeEmailRequest;
use App\Models\BotUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/** Подтверждение почты: страница «Подтвердите почту», повторная отправка и переход по ссылке из письма. */
class EmailVerificationController extends Controller
{
    public function notice(): View|RedirectResponse
    {
        /** @var BotUser $user */
        $user = Auth::guard('participant')->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('account.onboarding');
        }

        return view('account.auth.verify', ['user' => $user]);
    }

    public function resend(Request $request): RedirectResponse
    {
        /** @var BotUser $user */
        $user = Auth::guard('participant')->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('account.onboarding');
        }

        try {
            $user->sendEmailVerification();
        } catch (\Throwable $e) {
            Log::warning('Не удалось повторно отправить письмо подтверждения почты', ['user' => $user->id, 'error' => $e->getMessage()]);

            return back()->with('error', __('participant.verification.invalid'));
        }

        return back()->with('success', __('participant.verification.resent'));
    }

    /**
     * Ввод 5-значного кода из письма на этой же странице: результат тот же, что у ссылки. Код живёт 15 минут, неверно можно
     * ввести 5 раз (потом нужен новый), плюс ограничение частоты запросов в маршруте.
     */
    public function code(Request $request): RedirectResponse
    {
        /** @var BotUser $user */
        $user = Auth::guard('participant')->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('account.onboarding');
        }

        $code = preg_replace('/\D/', '', (string) $request->input('code'));

        if (strlen($code) !== 5) {
            return back()->withErrors(['code' => __('participant.verification.code_short')]);
        }

        return match ($user->checkEmailCode($code)) {
            'ok' => redirect()->route('account.onboarding')->with('success', __('participant.verification.verified')),
            'wrong' => back()->withErrors(['code' => __('participant.verification.code_wrong', ['left' => $user->emailCodeAttemptsLeft()])]),
            default => back()->withErrors(['code' => __('participant.verification.code_expired')]),
        };
    }

    /**
     * Смена почты, пока она не подтверждена: человек ошибся адресом при регистрации. Письмо уходит на новый адрес, а старые
     * ссылки перестают действовать (подпись ссылки зависит от адреса).
     */
    public function changeEmail(ChangeEmailRequest $request): RedirectResponse
    {
        /** @var BotUser $user */
        $user = Auth::guard('participant')->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('account.onboarding');
        }

        $email = (string) $request->validated('email');

        if ($email !== $user->email) {
            $user->forceFill(['email' => $email])->save();
        }

        try {
            $user->sendEmailVerification();
        } catch (\Throwable $e) {
            Log::warning('Не удалось отправить письмо подтверждения на исправленную почту', ['user' => $user->id, 'error' => $e->getMessage()]);

            return back()->with('error', __('participant.verification.invalid'));
        }

        return back()->with('success', __('participant.verification.email_changed', ['email' => $email]));
    }

    /**
     * Ссылка из письма (подписана Laravel и действует сутки). Работает и в другом браузере, где участница не вошла:
     * тогда после подтверждения её просят войти.
     */
    public function verify(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = BotUser::find($id);

        if ($user === null || ! hash_equals(sha1((string) $user->email), $hash)) {
            return redirect()->route('account.login')->with('error', __('participant.verification.invalid'));
        }

        $user->markEmailAsVerified();

        $guard = Auth::guard('participant');

        if ($guard->check() && $guard->id() === $user->id) {
            return redirect()->route('account.onboarding')->with('success', __('participant.verification.verified'));
        }

        // Другой браузер (письмо открыли на телефоне, а регистрировались на компьютере): сессии здесь нет, поэтому просим
        // войти, подставляем почту и на странице входа прячем «Я здесь впервые», чтобы человек не регистрировался заново.
        return redirect()->route('account.login')
            ->withInput(['email' => (string) $user->email])
            ->with('success', __('participant.verification.verified_login'))
            ->with('email_verified', true);
    }
}
