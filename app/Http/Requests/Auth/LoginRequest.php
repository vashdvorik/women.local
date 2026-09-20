<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Перебор пароля ограничивается двумя счётчиками:
     *
     *  - «почта + IP» — 5 попыток в минуту. Быстро гасит одну машину.
     *  - «почта» (со всех адресов) — 20 попыток за 15 минут. Это заслон от
     *    распределённого перебора: сколько бы IP атакующий ни менял, вход
     *    в аккаунт всё равно ограничен 20 попытками на окно. Учётка одна,
     *    поэтому глобального ключа «на всех» достаточно — ключ по самой почте.
     *
     * Оба счётчика обнуляются при первом же успешном входе. Цена — при активной
     * атаке владелец тоже увидит блокировку на входе (существующая 30-дневная
     * сессия при этом продолжает работать); ручной сброс — `php artisan cache:clear`.
     */
    private const MAX_PER_IP = 5;

    private const MAX_GLOBAL = 20;

    private const GLOBAL_DECAY_SECONDS = 900;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());
            RateLimiter::hit($this->globalThrottleKey(), self::GLOBAL_DECAY_SECONDS);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        RateLimiter::clear($this->globalThrottleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        $blockedKey = match (true) {
            RateLimiter::tooManyAttempts($this->globalThrottleKey(), self::MAX_GLOBAL) => $this->globalThrottleKey(),
            RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_PER_IP) => $this->throttleKey(),
            default => null,
        };

        if ($blockedKey === null) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($blockedKey);

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Ключ счётчика «почта + IP».
     */
    public function throttleKey(): string
    {
        return $this->normalizedEmail().'|'.$this->ip();
    }

    /**
     * Ключ счётчика «почта» — общий для всех адресов.
     */
    public function globalThrottleKey(): string
    {
        return 'login:'.$this->normalizedEmail();
    }

    private function normalizedEmail(): string
    {
        return Str::transliterate(Str::lower($this->string('email')));
    }
}
