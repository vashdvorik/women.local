<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Support\CardTone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Карточка новости: страница /events («Новости» в меню сайта), превью на главной и раздел «Новости»
 * в админке. Плоская модель: обложка, цвет, дата начала, ссылка, порядок и переводимые тексты
 * (EventTranslation). Историческое имя модели — Event.
 */
class Event extends Model
{
    use HasTranslations;

    /** Формат даты по языку: «18 июня», «June 18», «18 iunie». */
    private const DATE_FORMATS = ['ru' => 'D MMMM', 'ro' => 'D MMMM', 'en' => 'MMMM D'];

    protected $fillable = [
        'image_path',
        'tone',
        'starts_at',
        'url',
        'is_published',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'is_published' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderBy('id');
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? '/uploads/'.$this->image_path : null;
    }

    public function toneKey(): string
    {
        return CardTone::normalize($this->tone);
    }

    /**
     * Подпись даты на карточке: плашка редактора («Открыт набор») главнее,
     * иначе дата начала, отформатированная на языке карточки.
     */
    public function dateLabel(string $locale): string
    {
        $custom = $this->field('date_label', $locale);

        if (filled($custom)) {
            return $custom;
        }

        if ($this->starts_at === null) {
            return '';
        }

        $format = self::DATE_FORMATS[$locale] ?? self::DATE_FORMATS['ru'];

        return $this->starts_at->copy()->locale($locale)->isoFormat($format);
    }
}
