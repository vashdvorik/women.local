<?php

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Правила адреса страницы (AGENTS.md §11, правило 25):
 *
 *  - ничего не вводили — адрес формируется из русского заголовка;
 *  - заголовка ещё нет — временный `draft-xxxxxxxxxxxx` (для альбома `album-…`);
 *  - как только появляется заголовок, временный адрес заменяется осмысленным;
 *  - введённый вручную нормализуется: нижний регистр, дефисы вместо пробелов;
 *  - адрес уникален; при попытке занять чужой — ошибка валидации;
 *  - осмысленный адрес, однажды заданный, больше не перезаписывается.
 */
class ResolveSlug
{
    /**
     * @param  Model  $model         сохраняемая запись (может быть несохранённой)
     * @param  ?string  $submitted    что ввёл редактор в поле адреса
     * @param  ?string  $primaryTitle русский заголовок
     * @param  string  $temporaryPrefix `draft` для материалов, `album` для альбомов
     */
    public function handle(Model $model, ?string $submitted, ?string $primaryTitle, string $temporaryPrefix = 'draft'): string
    {
        $current = $model->slug;
        $submitted = trim((string) $submitted);

        if ($submitted !== '') {
            $normalized = Str::slug($submitted, '-', 'ru');

            if ($normalized === '') {
                throw ValidationException::withMessages([
                    'slug' => 'Адрес страницы не может состоять из одних недопустимых символов.',
                ]);
            }

            return $normalized === $current
                ? $current
                : $this->ensureUnique($model, $normalized, allowSuffix: false);
        }

        // Осмысленный адрес не перезаписываем.
        if ($current !== null && ! $this->isTemporary($current)) {
            return $current;
        }

        $title = trim((string) $primaryTitle);

        if ($title !== '') {
            $base = Str::slug($title, '-', 'ru');

            if ($base !== '') {
                return $this->ensureUnique($model, $base, allowSuffix: true);
            }
        }

        // Заголовка ещё нет — оставляем/создаём временный адрес.
        return $current ?? $temporaryPrefix.'-'.Str::lower(Str::random(12));
    }

    public function isTemporary(string $slug): bool
    {
        return (bool) preg_match('/^(draft|album)-[a-z0-9]{12}$/', $slug);
    }

    private function ensureUnique(Model $model, string $base, bool $allowSuffix): string
    {
        $exists = fn (string $slug) => $model->newQuery()
            ->where('slug', $slug)
            ->when($model->exists, fn ($q) => $q->whereKeyNot($model->getKey()))
            ->exists();

        if (! $exists($base)) {
            return $base;
        }

        if (! $allowSuffix) {
            throw ValidationException::withMessages([
                'slug' => 'Этот адрес уже занят другим материалом.',
            ]);
        }

        $i = 2;
        while ($exists($base.'-'.$i)) {
            $i++;
        }

        return $base.'-'.$i;
    }
}
