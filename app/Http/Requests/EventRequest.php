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
            'slug' => ['nullable', 'string', 'max:191'],
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
            $rules["translations.$locale.content"] = ['nullable', 'array'];
        }

        // Русское название обязательно: остальные языки подставят его сами.
        $rules['translations.ru.title'] = ['required', 'string', 'max:191'];

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'translations.ru.title' => 'название по-русски',
            'slug' => 'адрес страницы',
            'starts_at' => 'дата начала',
            'url' => 'ссылка «Подробнее»',
            'tone' => 'цвет карточки',
        ];
    }

    /** Блоки текста приходят строкой JSON из скрытого поля: разбираем до валидации. */
    protected function prepareForValidation(): void
    {
        $translations = (array) $this->input('translations', []);

        foreach ($translations as $locale => $data) {
            if (isset($data['content']) && is_string($data['content'])) {
                $decoded = json_decode($data['content'], true);
                $translations[$locale]['content'] = is_array($decoded) ? $decoded : [];
            }
        }

        $this->merge([
            'is_published' => $this->boolean('is_published'),
            'translations' => $translations,
        ]);
    }
}
