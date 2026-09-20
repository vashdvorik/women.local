<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Support\CardTone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Карточка эксперта на странице «Эксперты» и в превью на главной. Плоская модель:
 * портрет, цвет подложки, порядок и переводимые тексты (name/role/… в ExpertTranslation).
 */
class Expert extends Model
{
    use HasTranslations;

    protected $fillable = [
        'photo_path',
        'tone',
        'is_published',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /** Порядок на сайте: заданный редактором, затем по id. */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderBy('id');
    }

    public function photoUrl(): ?string
    {
        return $this->photo_path ? '/uploads/'.$this->photo_path : null;
    }

    public function toneKey(): string
    {
        return CardTone::normalize($this->tone);
    }
}
