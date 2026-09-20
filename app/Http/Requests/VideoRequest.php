<?php

namespace App\Http\Requests;

use App\Support\Locales;
use App\Support\YouTube;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class VideoRequest extends FormRequest
{
    public ?string $youtubeId = null;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'youtube_url' => ['required', 'string', 'max:255'],
            'event_date' => ['nullable', 'date'],
            'cover_path' => ['nullable', 'string'],
            'translations' => ['array'],
        ];

        foreach (Locales::ALL as $locale) {
            $rules["translations.$locale.title"] = ['nullable', 'string', 'max:191'];
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $id = YouTube::id((string) $this->input('youtube_url'));

            if ($id === null) {
                $validator->errors()->add('youtube_url', 'Укажите корректную ссылку на видео YouTube.');

                return;
            }

            $this->youtubeId = $id;

            $query = \App\Models\Video::where('youtube_id', $id);
            if ($this->route('video')) {
                $query->whereKeyNot($this->route('video')->getKey());
            }

            if ($query->exists()) {
                $validator->errors()->add('youtube_url', 'Видео с таким идентификатором уже добавлено.');
            }
        });
    }
}
