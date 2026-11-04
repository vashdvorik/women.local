<?php

declare(strict_types=1);

namespace App\Http\Requests\Account;

use App\Support\EmailTypo;
use Illuminate\Contracts\Validation\Validator;
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

    /** Почта уже есть: запоминаем её, чтобы кнопка «Войти» на странице регистрации подставила её в форму входа. */
    protected function failedValidation(Validator $validator): void
    {
        if ($validator->errors()->first('email') === __('participant.validation.email_taken')) {
            session()->put('login_email', (string) $this->input('email'));
        }

        parent::failedValidation($validator);
    }

    public function withValidator(Validator $validator): void
    {
        EmailTypo::guard($validator, (string) $this->input('email'), __('participant.validation.email_typo'));
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
