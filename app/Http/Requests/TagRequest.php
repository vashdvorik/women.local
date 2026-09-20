<?php

namespace App\Http\Requests;

use App\Support\Locales;
use Illuminate\Foundation\Http\FormRequest;

class TagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            // Невалидный HEX не сохраняется (AGENTS.md §15).
            'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'names' => ['array'],
        ];

        foreach (Locales::ALL as $locale) {
            $rules["names.$locale"] = ['required', 'string', 'max:80'];
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'color' => 'цвет',
            'names.ru' => 'название по-русски',
            'names.ro' => 'название по-румынски',
            'names.en' => 'название по-английски',
        ];
    }
}
