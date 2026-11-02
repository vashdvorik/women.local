<?php

declare(strict_types=1);

namespace App\Http\Requests\Account;

use App\Support\EmailTypo;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Смена почты на странице «Подтвердите почту»: для тех, кто ошибся адресом при регистрации. */
class ChangeEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::guard('participant')->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['email' => Str::lower(trim((string) $this->input('email')))]);
    }

    public function rules(): array
    {
        $userId = Auth::guard('participant')->id();

        return [
            'email' => ['required', 'string', 'email:rfc', 'max:191', Rule::unique('bot_users', 'email')->ignore($userId)],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('participant.validation.email_required'),
            'email.email' => __('participant.validation.email_invalid'),
            'email.max' => __('participant.validation.email_invalid'),
            'email.unique' => __('participant.validation.email_taken'),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        EmailTypo::guard($validator, (string) $this->input('email'), __('participant.validation.email_typo'));
    }
}
