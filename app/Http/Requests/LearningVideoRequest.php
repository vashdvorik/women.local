<?php

namespace App\Http\Requests;

use App\Models\LearningVideo;
use App\Support\Locales;
use App\Support\YouTube;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/** Обучающее видео для кабинета: ссылка YouTube, название и описание на трёх языках, обложка, «показывать». */
class LearningVideoRequest extends FormRequest
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
            'cover_path' => ['nullable', 'string'],
            'is_published' => ['nullable', 'boolean'],
            'translations' => ['array'],
        ];

        foreach (Locales::ALL as $locale) {
            $rules["translations.$locale.title"] = ['nullable', 'string', 'max:191'];
            $rules["translations.$locale.description"] = ['nullable', 'string', 'max:2000'];
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $id = YouTube::id((string) $this->input('youtube_url'));

            if ($id === null) {
                $validator->errors()->add('youtube_url', __('Укажите корректную ссылку на видео YouTube.'));

                return;
            }

            $this->youtubeId = $id;

            $query = LearningVideo::where('youtube_id', $id);
            if ($this->route('learningVideo')) {
                $query->whereKeyNot($this->route('learningVideo')->getKey());
            }

            if ($query->exists()) {
                $validator->errors()->add('youtube_url', __('Видео с таким идентификатором уже добавлено.'));
            }

            // Русское название обязательно: на него подставляются другие языки, а в списке по нему узнают видео.
            if (blank($this->input('translations.'.Locales::PRIMARY.'.title'))) {
                $validator->errors()->add('translations.'.Locales::PRIMARY.'.title', __('Укажите название по-русски.'));
            }
        });
    }
}
