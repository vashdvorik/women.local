<?php

declare(strict_types=1);

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/** Регистрация участницы: почта, пароль и согласие. Анкета заполняется на следующем шаге. */
class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['email' => Str::lower(trim((string) $this->input('email')))]);
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:rfc', 'max:191', 'unique:bot_users,email'],
            'password' => ['required', 'string', 'min:8', 'max:128', 'confirmed'],
            'consent' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('participant.validation.email_required'),
            'email.email' => __('participant.validation.email_invalid'),
            'email.max' => __('participant.validation.email_invalid'),
            'email.unique' => __('participant.validation.email_taken'),
            'password.required' => __('participant.validation.password_required'),
            'password.min' => __('participant.validation.password_min'),
            'password.confirmed' => __('participant.validation.password_confirmed'),
            'consent.accepted' => __('participant.validation.consent'),
        ];
    }
}
