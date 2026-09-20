<?php

namespace App\Http\Requests;

use App\Support\CardTone;
use App\Support\Locales;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'image_path' => ['nullable', 'string'],
            'tone' => ['required', Rule::in(CardTone::keys())],
            'starts_at' => ['nullable', 'date'],
            'url' => ['nullable', 'url', 'max:2000'],
            'is_published' => ['boolean'],
            'translations' => ['array'],
        ];

        foreach (Locales::ALL as $locale) {
            $rules["translations.$locale.type"] = ['nullable', 'string', 'max:120'];
            $rules["translations.$locale.date_label"] = ['nullable', 'string', 'max:120'];
            $rules["translations.$locale.title"] = ['nullable', 'string', 'max:191'];
            $rules["translations.$locale.description"] = ['nullable', 'string', 'max:2000'];
        }

        // Русское название обязательно: остальные языки подставят его сами.
        $rules['translations.ru.title'] = ['required', 'string', 'max:191'];

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'translations.ru.title' => 'название по-русски',
            'starts_at' => 'дата начала',
            'url' => 'ссылка «Подробнее»',
            'tone' => 'цвет карточки',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_published' => $this->boolean('is_published')]);
    }
}
