<?php

declare(strict_types=1);

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return session()->has('account_telegram_id');
    }

    public function rules(): array
    {
        return [
            'full_name'   => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'expectation' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Введите имя и фамилию.',
            'full_name.max'      => 'Имя не должно быть длиннее 120 символов.',
            'description.max'    => 'Описание бизнеса не должно быть длиннее 1000 символов.',
            'expectation.max'    => 'Запросы и предложения не должны быть длиннее 1000 символов.',
        ];
    }
}
