<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Event;
use App\Models\Post;
use App\Models\SiteOpportunity;

/**
 * Лента для главной кабинета на тарифе Open: «только та информация, которая есть на публичном сайте».
 * Берёт последние опубликованные материалы тех же разделов, что показывает сайт (новости, публикации, возможности), и
 * отдаёт карточки с готовыми ссылками на публичные страницы. Ничего из закрытой части сюда не попадает.
 */
final class OpenFeed
{
    /**
     * @return array{news: list<array<string, mixed>>, publications: list<array<string, mixed>>, opportunities: list<array<string, mixed>>}
     */
    public static function build(string $locale, int $limit = 3): array
    {
        $news = Event::published()->ordered()->with('translations')->limit($limit)->get()
            ->map(function (Event $event) use ($locale): array {
                $link = $event->cardLink();

                return [
                    'title' => self::pick(PublicCards::field($event, 'title'), $locale),
                    'excerpt' => self::pick(PublicCards::field($event, 'description'), $locale),
                    'date' => $event->dateLabel($locale),
                    'href' => $link['href'] ?? route('events'),
                    'external' => (bool) ($link['external'] ?? false),
                ];
            })->all();

        $publications = Post::with(['translations', 'tag.translations'])->published()->orderByDesc('published_at')->limit($limit)->get()
            ->map(fn (Post $post): array => self::fromCard(PublicCards::post($post), $locale))->all();

        $opportunities = SiteOpportunity::with(['translations', 'tag.translations'])->published()->orderByDesc('published_at')->limit($limit)->get()
            ->map(fn (SiteOpportunity $item): array => self::fromCard(PublicCards::opportunity($item), $locale))->all();

        return compact('news', 'publications', 'opportunities');
    }

    /**
     * @param  array<string, mixed>  $card  карточка из PublicCards
     * @return array<string, mixed>
     */
    private static function fromCard(array $card, string $locale): array
    {
        return [
            'title' => self::pick($card['title'] ?? [], $locale),
            'excerpt' => self::pick($card['excerpt'] ?? [], $locale),
            'date' => isset($card['date']) && is_array($card['date']) ? self::pick($card['date'], $locale) : '',
            'href' => (string) ($card['href'] ?? '#'),
            'external' => (bool) ($card['external'] ?? false),
        ];
    }

    /** Текст на нужном языке; если перевода нет — русский. */
    private static function pick(array $texts, string $locale): string
    {
        $text = trim((string) ($texts[$locale] ?? ''));

        return $text !== '' ? $text : trim((string) ($texts['ru'] ?? ''));
    }
}
