<?php

namespace App\Http\Requests;

use App\Support\Locales;
use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'image_path' => ['nullable', 'string'],
            'url' => ['nullable', 'url', 'max:2000'],
            'is_published' => ['boolean'],
            'translations' => ['array'],
        ];

        foreach (Locales::ALL as $locale) {
            $rules["translations.$locale.title"] = ['nullable', 'string', 'max:191'];
            $rules["translations.$locale.category"] = ['nullable', 'string', 'max:120'];
            $rules["translations.$locale.text"] = ['nullable', 'string', 'max:2000'];
        }

        // Русский заголовок обязателен (AGENTS.md §13).
        $rules['translations.ru.title'] = ['required', 'string', 'max:191'];

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'translations.ru.title' => 'название по-русски',
            'url' => 'ссылка на сайт проекта',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_published' => $this->boolean('is_published')]);
    }
}
