<?php

namespace App\Http\Requests;

use App\Support\Locales;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Общая валидация новости и возможности. Черновик сохраняется с любым уровнем
 * заполнения — почти всё nullable. Проверки публикации живут отдельно
 * (PublishArticle), здесь их нет (AGENTS.md §11).
 */
abstract class ArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Блоки приходят строкой JSON из скрытого textarea — разбираем до валидации.
     */
    protected function prepareForValidation(): void
    {
        $translations = (array) $this->input('translations', []);

        foreach ($translations as $locale => $data) {
            if (isset($data['content']) && is_string($data['content'])) {
                $decoded = json_decode($data['content'], true);
                $translations[$locale]['content'] = is_array($decoded) ? $decoded : [];
            }
        }

        $this->merge(['translations' => $translations]);
    }

    public function rules(): array
    {
        $rules = [
            'slug' => ['nullable', 'string', 'max:191'],
            // Обложка одна на все языки; путь кладёт загрузчик скрытым полем.
            // Без правила `validated()` его отбрасывает — и обложка не сохраняется.
            'cover_path' => ['nullable', 'string', 'max:255'],
            'author' => ['nullable', 'string', 'max:191'],
            'published_at' => ['nullable', 'date'],
            'tag_id' => ['nullable', 'integer', 'exists:tags,id'],
            'translations' => ['array'],
        ];

        foreach (Locales::ALL as $locale) {
            $rules["translations.$locale.title"] = ['nullable', 'string', 'max:191'];
            $rules["translations.$locale.excerpt"] = ['nullable', 'string', 'max:2000'];
            $rules["translations.$locale.seo_title"] = ['nullable', 'string', 'max:191'];
            $rules["translations.$locale.seo_description"] = ['nullable', 'string', 'max:240'];
            $rules["translations.$locale.content"] = ['nullable', 'array'];
        }

        return array_merge($rules, $this->extraRules());
    }

    /** Дополнительные правила подкласса (у возможности — срок и тег). */
    protected function extraRules(): array
    {
        return [];
    }
}
