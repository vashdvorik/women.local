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
            // Фото профиля: необязательно; квадрат 512 px режется из него автоматически (App\Services\AvatarStore).
            'photo'        => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192', 'dimensions:max_width=8000,max_height=8000'],
            'remove_photo' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => __('account.validation.full_name_required'),
            'full_name.max'      => __('account.validation.full_name_max'),
            'description.max'    => __('account.validation.description_max'),
            'expectation.max'    => __('account.validation.expectation_max'),
            'photo.uploaded'     => __('account.validation.photo_max'),
            'photo.max'          => __('account.validation.photo_max'),
            'photo.image'        => __('account.validation.photo_invalid'),
            'photo.mimes'        => __('account.validation.photo_invalid'),
            'photo.dimensions'   => __('account.validation.photo_max'),
        ];
    }
}
