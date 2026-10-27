<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
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

        return redirect()->route('account.login')->with('success', __('participant.verification.verified'));
    }
}
