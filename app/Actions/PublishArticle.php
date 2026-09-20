<?php

namespace App\Actions;

use App\Support\Locales;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * Переход между «черновик» и «опубликовано». Проверки срабатывают только при
 * публикации; статус не меняется, если они не прошли (AGENTS.md §11).
 */
class PublishArticle
{
    public function publish(Model $article): void
    {
        $article->loadMissing('translations');
        $ru = $article->translations->firstWhere('locale', Locales::PRIMARY);

        $errors = [];

        if (blank($ru?->title)) {
            $errors['translations.ru.title'] = 'Добавьте русский заголовок.';
        }

        if (blank($ru?->excerpt)) {
            $errors['translations.ru.excerpt'] = 'Добавьте краткое описание.';
        }

        if ($article->published_at && Carbon::parse($article->published_at)->isFuture()) {
            $errors['published_at'] = 'Дата публикации не может быть в будущем.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors)
                ->errorBag('default');
        }

        $article->status = 'published';
        $article->published_at ??= now();
        $article->save();
    }

    public function unpublish(Model $article): void
    {
        $article->status = 'draft';
        $article->published_at = null;
        $article->save();
    }
}
