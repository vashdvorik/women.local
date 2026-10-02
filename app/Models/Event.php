<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Support\CardTone;
use App\Support\Locales;
use App\Support\TranslationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Новость: карточка на странице /events («Новости» в меню сайта) и в превью на главной, а также своя
 * страница /events/{slug}. Карточка: обложка, цвет, дата начала, ссылка, порядок и переводимые тексты
 * (EventTranslation). Страница: текст из блоков на каждом языке (EventTranslation::content), тот же
 * формат, что у публикаций. Раздел «Новости» в админке. Историческое имя модели — Event.
 */
class Event extends Model
{
    use HasTranslations;

    /** Формат даты по языку: «18 июня», «June 18», «18 iunie». */
    private const DATE_FORMATS = ['ru' => 'D MMMM', 'ro' => 'D MMMM', 'en' => 'MMMM D'];

    protected $fillable = [
        'slug',
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

    /** Стиль плашки типа («Конференция») в цвет карточки; синий — приглушённый, чтобы читалась подпись. */
    public function tagStyle(): string
    {
        $tone = $this->toneKey();

        return $tone === 'blue'
            ? 'background:var(--miro-surface-featured);color:var(--miro-blue)'
            : 'background:var(--miro-'.$tone.');color:var(--miro-primary)';
    }

    /** Есть ли у новости текст страницы (на русском: он главный, остальные языки подставляют его). */
    public function hasBody(): bool
    {
        return TranslationStatus::hasContent($this->rawTranslation(Locales::PRIMARY)?->content ?? []);
    }

    /**
     * Куда ведёт «Подробнее» на карточке: на собственную страницу новости, если у неё есть текст,
     * иначе по внешней ссылке (в новой вкладке). null — вести некуда, кнопки нет.
     *
     * @return array{href: string, external: bool}|null
     */
    public function cardLink(): ?array
    {
        if (filled($this->slug) && $this->hasBody()) {
            return ['href' => route('events.show', ['event' => $this->slug]), 'external' => false];
        }

        return filled($this->url) ? ['href' => $this->url, 'external' => true] : null;
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
