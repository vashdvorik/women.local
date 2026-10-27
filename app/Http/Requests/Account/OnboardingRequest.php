<?php

declare(strict_types=1);

namespace App\Http\Requests\Account;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/** Анкета после регистрации: те же вопросы, что при подаче заявки, и обязательный телефон. */
class OnboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('participant')->check();
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:32', self::phoneRule()],
            'description' => ['required', 'string', 'max:1000'],
            'expectation' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => __('participant.validation.full_name_required'),
            'full_name.max' => __('account.validation.full_name_max'),
            'phone.required' => __('participant.validation.phone_required'),
            'phone.max' => __('participant.validation.phone_invalid'),
            'description.required' => __('participant.validation.description_required'),
            'description.max' => __('account.validation.description_max'),
            'expectation.required' => __('participant.validation.expectation_required'),
            'expectation.max' => __('account.validation.expectation_max'),
        ];
    }

    /** Телефон: цифры, можно с «+», пробелами, скобками и дефисами; от 8 до 15 цифр (международный формат). */
    public static function phoneRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $value = (string) $value;
            $digits = preg_replace('/\D/', '', $value) ?? '';

            if (! preg_match('/^\+?[0-9\s().\-]+$/', $value) || strlen($digits) < 8 || strlen($digits) > 15) {
                $fail(__('participant.validation.phone_invalid'));
            }
        };
    }
}
