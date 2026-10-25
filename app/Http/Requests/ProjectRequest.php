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
            'slug' => ['nullable', 'string', 'max:191', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'is_published' => ['boolean'],
            'translations' => ['array'],
        ];

        foreach (Locales::ALL as $locale) {
            $rules["translations.$locale.title"] = ['nullable', 'string', 'max:191'];
            $rules["translations.$locale.category"] = ['nullable', 'string', 'max:120'];
            $rules["translations.$locale.text"] = ['nullable', 'string', 'max:2000'];
            $rules["translations.$locale.content"] = ['nullable', 'array'];
        }

        // Русский заголовок обязателен (AGENTS.md §13).
        $rules['translations.ru.title'] = ['required', 'string', 'max:191'];

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'translations.ru.title' => __('название по-русски'),
            'url' => __('ссылка на сайт проекта'),
            'slug' => __('адрес страницы'),
        ];
    }

    protected function prepareForValidation(): void
    {
        // Блоки текста приходят строкой JSON из скрытого поля: разбираем до валидации
        $translations = (array) $this->input('translations', []);

        foreach ($translations as $locale => $data) {
            if (isset($data['content']) && is_string($data['content'])) {
                $decoded = json_decode($data['content'], true);
                $translations[$locale]['content'] = is_array($decoded) ? $decoded : [];
            }
        }

        $this->merge(['is_published' => $this->boolean('is_published'), 'translations' => $translations]);
    }
}
