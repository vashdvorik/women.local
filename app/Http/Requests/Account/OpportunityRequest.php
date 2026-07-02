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
            'type.required'             => __('account.validation.type_required'),
            'type.in'                   => __('account.validation.type_in'),
            'title.required'            => __('account.validation.title_required'),
            'title.max'                 => __('account.validation.title_max'),
            'body.required'             => __('account.validation.body_required'),
            'body.max'                  => __('account.validation.body_max'),
            'event_date.date'           => __('account.validation.event_date_date'),
            'event_date.after_or_equal' => __('account.validation.event_date_future'),
            'contact_url.url'           => __('account.validation.contact_url_url'),
            'contact_url.max'           => __('account.validation.contact_url_max'),
        ];
    }
}
