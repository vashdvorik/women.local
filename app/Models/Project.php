<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

/**
 * Карточка проекта на странице «Проекты». Плоская модель: картинка и
 * переводимые «название»/«категория»/«текст» (AGENTS.md §13).
 */
class Project extends Model
{
    use HasTranslations;

    protected $fillable = [
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

    public function imageUrl(): ?string
    {
        return $this->image_path ? '/uploads/'.$this->image_path : null;
    }
}
