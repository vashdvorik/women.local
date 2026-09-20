<?php

namespace App\Http\Requests;

use App\Support\CardTone;
use App\Support\Locales;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExpertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'photo_path' => ['nullable', 'string'],
            'tone' => ['required', Rule::in(CardTone::keys())],
            'is_published' => ['boolean'],
            'translations' => ['array'],
        ];

        foreach (Locales::ALL as $locale) {
            $rules["translations.$locale.name"] = ['nullable', 'string', 'max:191'];
            $rules["translations.$locale.role"] = ['nullable', 'string', 'max:255'];
            $rules["translations.$locale.specialization"] = ['nullable', 'string', 'max:255'];
            $rules["translations.$locale.description"] = ['nullable', 'string', 'max:2000'];
            $rules["translations.$locale.looking_for"] = ['nullable', 'string', 'max:1000'];
            $rules["translations.$locale.can_offer"] = ['nullable', 'string', 'max:1000'];
            $rules["translations.$locale.tags"] = ['nullable', 'string', 'max:500'];
        }

        // Русское имя обязательно: остальные языки подставят его сами.
        $rules['translations.ru.name'] = ['required', 'string', 'max:191'];

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'translations.ru.name' => 'имя по-русски',
            'tone' => 'цвет карточки',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_published' => $this->boolean('is_published')]);
    }
}
