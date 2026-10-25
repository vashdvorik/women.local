<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

/**
 * Карточка проекта на странице «Проекты»: картинка и переводимые «название»/«категория»/«текст»
 * (AGENTS.md §13). У проекта может быть и своя страница (/projects/{slug}): текст блоками и фотографии,
 * как у публикаций; карточка тогда ведёт на неё, а не на внешнюю ссылку.
 */
class Project extends Model
{
    use HasTranslations;

    protected $fillable = [
        'slug',
        'image_path',
        'url',
        'is_published',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    /** Есть ли у проекта своя страница: адрес и текст страницы хотя бы на русском. */
    public function hasPage(): bool
    {
        return filled($this->slug)
            && \App\Support\TranslationStatus::hasContent($this->rawTranslation(\App\Support\Locales::PRIMARY)?->content ?? []);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? '/uploads/'.$this->image_path : null;
    }
}
