<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\BotUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Быстрый вход и регистрация через Google (OAuth 2.0, код авторизации). Без внешних пакетов: два запроса к Google.
 * Ключи (GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET) лежат в .env; пока их нет, кнопка на страницах входа не показывается.
 *
 * Участница, чья почта уже есть в базе, входит в тот же кабинет (Google подтверждает почту); новой создаётся кабинет
 * с подтверждённой почтой, дальше она заполняет анкету, как и после обычной регистрации.
 */
class GoogleController extends Controller
{
    private const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const USERINFO_URL = 'https://openidconnect.googleapis.com/v1/userinfo';

    public static function enabled(): bool
    {
        return filled(config('services.google.client_id')) && filled(config('services.google.client_secret'));
    }

    public function redirect(Request $request): RedirectResponse
    {
        abort_unless(self::enabled(), 404);

        $state = Str::random(40);
        $request->session()->put('google_oauth_state', $state);

        return redirect()->away(self::AUTH_URL.'?'.http_build_query([
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => route('account.google.callback'),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'prompt' => 'select_account',
        ]));
    }

    public function callback(Request $request): RedirectResponse
    {
        abort_unless(self::enabled(), 404);

        $expected = (string) $request->session()->pull('google_oauth_state', '');

        if ($expected === '' || ! hash_equals($expected, (string) $request->query('state', '')) || $request->query('error') || ! $request->query('code')) {
            return $this->failed(__('participant.google.failed'));
        }

        try {
            $token = Http::asForm()->timeout(15)->post(self::TOKEN_URL, [
                'code' => (string) $request->query('code'),
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'redirect_uri' => route('account.google.callback'),
                'grant_type' => 'authorization_code',
            ])->throw()->json('access_token');

            $profile = Http::withToken((string) $token)->timeout(15)->get(self::USERINFO_URL)->throw()->json();
        } catch (\Throwable $e) {
            Log::warning('Вход через Google не удался', ['error' => $e->getMessage()]);

            return $this->failed(__('participant.google.failed'));
        }

        $googleId = (string) ($profile['sub'] ?? '');
        $email = Str::lower(trim((string) ($profile['email'] ?? '')));

        if ($googleId === '' || $email === '' || ! filter_var($profile['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return $this->failed(__('participant.google.no_email'));
        }

        $user = BotUser::where('google_id', $googleId)->first() ?? BotUser::where('email', $email)->first();

        if ($user === null) {
            $user = BotUser::create([
                'email' => $email,
                'full_name' => Str::limit(trim((string) ($profile['name'] ?? '')), 120, '') ?: null,
                'locale' => in_array(app()->getLocale(), ['ru', 'en', 'ro'], true) ? app()->getLocale() : 'ru',
            ]);
        }

        if ($user->isRejected()) {
            return $this->failed(__('participant.login.access_closed'));
        }

        // Google подтвердил почту: подтверждение письмом не нужно, а аккаунт привязывается к Google.
        $user->forceFill([
            'google_id' => $user->google_id ?? $googleId,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        Auth::guard('participant')->login($user, true);
        $request->session()->regenerate();
        $user->markCabinetOpened();

        return redirect()->intended(route('account.index'));
    }

    private function failed(string $message): RedirectResponse
    {
        return redirect()->route('account.login')->with('error', $message);
    }
}
