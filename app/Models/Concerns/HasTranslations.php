<?php

namespace App\Models\Concerns;

use App\Support\Locales;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Общая логика переводных моделей (Post, Opportunity, Album, Video).
 *
 * Подстановка живёт здесь: `translation()` отдаёт запрошенную локаль либо
 * русскую, если запрошенной нет. В редакторе подстановку применять нельзя —
 * там используется `rawTranslation()`, отдающий строго запрошенную локаль
 * (AGENTS.md §7, §13).
 */
trait HasTranslations
{
    public function translations(): HasMany
    {
        return $this->hasMany($this->translationModel());
    }

    /** Строго запрошенная локаль (или текущая), без подстановки. Может быть null. */
    public function rawTranslation(?string $locale = null): ?Model
    {
        $locale ??= app()->getLocale();

        return $this->translations->firstWhere('locale', $locale);
    }

    /** Запрошенная локаль либо русская — для публичной части. */
    public function translation(?string $locale = null): ?Model
    {
        return $this->rawTranslation($locale)
            ?? $this->rawTranslation(Locales::PRIMARY);
    }

    /** Значение поля перевода с подстановкой русского на уровне поля (AGENTS.md §7). */
    public function field(string $name, ?string $locale = null): ?string
    {
        $value = $this->rawTranslation($locale)?->{$name};

        return filled($value)
            ? $value
            : $this->rawTranslation(\App\Support\Locales::PRIMARY)?->{$name};
    }

    /**
     * Блоки для публичного показа: структура и картинки из русской версии, текст
     * из запрошенной локали там, где он заполнен, иначе — русский (AGENTS.md §7).
     *
     * @return array<int, array>
     */
    public function renderBlocks(?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        $primary = $this->rawTranslation(\App\Support\Locales::PRIMARY)?->content ?? [];

        if ($locale === \App\Support\Locales::PRIMARY) {
            return $primary;
        }

        $localized = collect($this->rawTranslation($locale)?->content ?? [])
            ->keyBy(fn ($b) => $b['uid'] ?? '');

        return array_map(function (array $block) use ($localized) {
            $counterpart = $localized->get($block['uid'] ?? '');
            $data = $block['data'];

            if (($block['type'] ?? null) === 'text') {
                $translated = trim(strip_tags($counterpart['data']['html'] ?? ''));
                if ($translated !== '') {
                    $data['html'] = $counterpart['data']['html'];
                }
            } elseif (($block['type'] ?? null) === 'heading') {
                $translated = trim($counterpart['data']['text'] ?? '');
                if ($translated !== '') {
                    $data['text'] = $counterpart['data']['text'];
                }
            }

            return ['uid' => $block['uid'], 'type' => $block['type'], 'data' => $data];
        }, $primary);
    }

    protected function translationModel(): string
    {
        return static::class.'Translation';
    }
}
