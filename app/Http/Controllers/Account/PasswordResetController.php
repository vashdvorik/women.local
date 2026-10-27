<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\BotUser;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

/** Восстановление пароля участницы по ссылке из письма (брокер паролей «participants»). */
class PasswordResetController extends Controller
{
    public function forgotForm(): View
    {
        return view('account.auth.forgot');
    }

    public function forgotSend(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email'],
        ], [
            'email.required' => __('participant.validation.email_required'),
            'email.email' => __('participant.validation.email_invalid'),
        ]);

        try {
            Password::broker('participants')->sendResetLink(['email' => Str::lower(trim($data['email']))]);
        } catch (\Throwable $e) {
            Log::warning('Не удалось отправить письмо для нового пароля', ['error' => $e->getMessage()]);
        }

        // Ответ один и тот же, есть такая почта или нет: так нельзя выяснить, кто зарегистрирован.
        return back()->with('success', __('participant.password.forgot_sent'));
    }

    public function resetForm(Request $request, string $token): View
    {
        return view('account.auth.reset', ['token' => $token, 'email' => (string) $request->query('email', '')]);
    }

    public function resetStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:8', 'max:128', 'confirmed'],
        ], [
            'email.required' => __('participant.validation.email_required'),
            'email.email' => __('participant.validation.email_invalid'),
            'password.required' => __('participant.validation.password_required'),
            'password.min' => __('participant.validation.password_min'),
            'password.confirmed' => __('participant.validation.password_confirmed'),
        ]);

        $status = Password::broker('participants')->reset(
            ['email' => Str::lower(trim($data['email'])), 'password' => $data['password'], 'password_confirmation' => $data['password'], 'token' => $data['token']],
            function (BotUser $user, string $password): void {
                // Ссылка пришла на почту, значит почта подтверждена.
                $user->forceFill(['password' => $password, 'remember_token' => Str::random(60), 'email_verified_at' => $user->email_verified_at ?? now()])->save();
                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withInput($request->only('email'))->with('error', __('participant.password.reset_invalid'));
        }

        return redirect()->route('account.login')->with('success', __('participant.password.reset_done'));
    }
}
