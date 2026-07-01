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
            'type.required'             => 'Выберите тип публикации.',
            'type.in'                   => 'Выбранный тип публикации недоступен.',
            'title.required'            => 'Введите короткий заголовок.',
            'title.max'                 => 'Заголовок не должен быть длиннее 200 символов.',
            'body.required'             => 'Опишите запрос, предложение или событие.',
            'body.max'                  => 'Описание не должно быть длиннее 2000 символов.',
            'event_date.date'           => 'Введите корректную дату.',
            'event_date.after_or_equal' => 'Дата не может быть в прошлом.',
            'contact_url.url'           => 'Введите корректную ссылку, например https://t.me/username.',
            'contact_url.max'           => 'Ссылка не должна быть длиннее 500 символов.',
        ];
    }
}
