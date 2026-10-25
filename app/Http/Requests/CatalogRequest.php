<?php

namespace App\Http\Requests;

use App\Support\Locales;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Validator;

class CatalogRequest extends FormRequest
{
    /** @var list<string> пути страниц из поля pages (JSON), проверенные в withValidator */
    public array $pagePaths = [];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $pages = $this->input('pages');

        if (is_string($pages)) {
            $decoded = json_decode($pages, true);
            $this->merge(['pages' => is_array($decoded) ? array_values($decoded) : []]);
        }
    }

    public function rules(): array
    {
        $rules = [
            'slug' => ['nullable', 'string', 'max:150', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'pages' => ['required', 'array', 'min:1', 'max:300'],
            'pages.*' => ['string', 'max:191'],
            'page_ratio' => ['nullable', 'numeric', 'between:0.2,5'],
            'cover_path' => ['nullable', 'string', 'max:191'],
            'pdf' => ['nullable', 'file', 'mimes:pdf', 'max:61440'],
            'allow_download' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
            'translations' => ['array'],
            'translations.ru.title' => ['required', 'string', 'max:191'],
        ];

        foreach (Locales::ALL as $locale) {
            $rules["translations.$locale.title"] ??= ['nullable', 'string', 'max:191'];
            $rules["translations.$locale.description"] = ['nullable', 'string', 'max:2000'];
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $paths = array_values(array_filter((array) $this->input('pages'), 'is_string'));

            foreach ($paths as $path) {
                // Только файлы страниц, загруженные в админке: каталог catalogs/…, без выхода из папки
                if (! str_starts_with($path, 'catalogs/') || str_contains($path, '..') || ! Storage::disk('uploads')->exists($path)) {
                    $validator->errors()->add('pages', __('Страница каталога не найдена на сервере. Загрузите PDF ещё раз.'));

                    return;
                }
            }

            $this->pagePaths = $paths;
        });
    }

    public function attributes(): array
    {
        return [
            'slug' => __('адрес страницы'),
            'pages' => __('страницы каталога'),
            'pdf' => 'PDF',
            'translations.ru.title' => __('название по-русски'),
        ];
    }
}
