<?php

declare(strict_types=1);

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class OpportunityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return session()->has('account_telegram_id');
    }

    public function rules(): array
    {
        return [
            'type'        => ['required', 'in:project,meeting,event'],
            'title'       => ['required', 'string', 'max:200'],
            'body'        => ['required', 'string', 'max:2000'],
            'event_date'  => ['nullable', 'date', 'after_or_equal:today'],
            'location'    => ['nullable', 'string', 'max:200'],
            'contact_url' => ['nullable', 'url', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required'             => 'Выберите тип возможности.',
            'type.in'                   => 'Недопустимый тип.',
            'title.required'            => 'Введите заголовок.',
            'title.max'                 => 'Заголовок не более 200 символов.',
            'body.required'             => 'Введите описание.',
            'body.max'                  => 'Описание не более 2000 символов.',
            'event_date.date'           => 'Введите корректную дату.',
            'event_date.after_or_equal' => 'Дата не может быть в прошлом.',
            'contact_url.url'           => 'Введите корректный URL (https://...).',
            'contact_url.max'           => 'URL не более 500 символов.',
        ];
    }
}
