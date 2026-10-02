<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Статус здесь не редактируется: он меняется кнопками «Одобрить» и «Отклонить» с уведомлением в боте. */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'expectation' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'full_name' => __('имя и фамилия'),
            'description' => __('описание'),
            'expectation' => __('запрос и предложение'),
        ];
    }
}
