<?php

namespace App\Http\Requests;

use App\Support\Locales;
use Illuminate\Foundation\Http\FormRequest;

class AlbumRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('blocks'))) {
            $decoded = json_decode($this->input('blocks'), true);
            $this->merge(['blocks' => is_array($decoded) ? $decoded : []]);
        }
    }

    public function rules(): array
    {
        $rules = [
            'slug' => ['nullable', 'string', 'max:191'],
            'cover_path' => ['nullable', 'string', 'max:255'],
            'published_at' => ['nullable', 'date'],
            'blocks' => ['nullable', 'array'],
            'translations' => ['array'],
        ];

        foreach (Locales::ALL as $locale) {
            $rules["translations.$locale.title"] = ['nullable', 'string', 'max:191'];
            $rules["translations.$locale.excerpt"] = ['nullable', 'string', 'max:2000'];
        }

        return $rules;
    }
}
