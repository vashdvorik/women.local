<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Материал — последовательность блоков, а не поле с текстом. Формат блока един:
 * { "uid": "...", "type": "...", "data": {} }.
 *
 * Структура блоков общая для всех языков (AGENTS.md §12): добавление, удаление
 * и перестановка на любой вкладке применяются ко всем языкам сразу. Переводится
 * только текст внутри; типы, порядок и пути к изображениям — из русской версии.
 */
class Blocks
{
    /** Блоки, в которых есть переводимый текст. */
    public const TEXT_KINDS = ['text', 'heading'];

    /** Блоки с изображениями (и их число ячеек). */
    public const IMAGE_KINDS = ['image', 'gallery_2', 'gallery_3', 'gallery_4'];

    /**
     * Полный набор для новостей и возможностей. `embed` — произвольный HTML,
     * который выводится на сайте без обработки (AGENTS.md §5.5); структурный,
     * то есть одинаковый для всех языков.
     */
    public const ARTICLE_KINDS = ['text', 'heading', 'embed', 'image', 'gallery_2', 'gallery_3', 'gallery_4'];

    /** Альбом — это фотографии, а не статья: только блоки с изображениями. */
    public const ALBUM_KINDS = ['image', 'gallery_2', 'gallery_3', 'gallery_4'];

    public static function cells(string $type): int
    {
        return match ($type) {
            'image' => 1,
            'gallery_2' => 2,
            'gallery_3' => 3,
            'gallery_4' => 4,
            default => 0,
        };
    }

    public static function isImageKind(string $type): bool
    {
        return in_array($type, self::IMAGE_KINDS, true);
    }

    /** Пустой текстовый блок — им открывается новый материал. */
    public static function emptyText(): array
    {
        return [
            'uid' => (string) Str::uuid(),
            'type' => 'text',
            'data' => ['html' => ''],
        ];
    }

    /**
     * Канонизировать структуру по русской версии: отбросить неизвестные типы,
     * привести data к ожидаемой форме, выдать каждому блоку стабильный uid.
     *
     * @param  array<int, array>  $blocks
     * @return array<int, array>
     */
    public static function canonical(array $blocks, array $allowedKinds): array
    {
        $result = [];

        foreach ($blocks as $block) {
            $type = $block['type'] ?? null;

            if (! is_string($type) || ! in_array($type, $allowedKinds, true)) {
                continue;
            }

            $uid = (string) ($block['uid'] ?? Str::uuid());
            $data = is_array($block['data'] ?? null) ? $block['data'] : [];

            $result[] = [
                'uid' => $uid,
                'type' => $type,
                'data' => self::canonicalData($type, $data),
            ];
        }

        return $result;
    }

    /**
     * Собрать блоки для языка перевода: структура и картинки из русской версии,
     * текст — из присланного перевода, сопоставление по uid.
     *
     * @param  array<int, array>  $primary     канонические русские блоки
     * @param  array<int, array>  $translated  присланные блоки языковой вкладки
     * @return array<int, array>
     */
    public static function mergeTranslation(array $primary, array $translated): array
    {
        $translatedByUid = collect($translated)->keyBy(fn ($b) => $b['uid'] ?? '');

        return array_map(function (array $block) use ($translatedByUid) {
            $incoming = $translatedByUid->get($block['uid']);
            $incomingData = is_array($incoming['data'] ?? null) ? $incoming['data'] : [];

            $data = $block['data'];

            if ($block['type'] === 'text') {
                $data['html'] = is_string($incomingData['html'] ?? null) ? $incomingData['html'] : '';
            } elseif ($block['type'] === 'heading') {
                $data['text'] = is_string($incomingData['text'] ?? null) ? trim($incomingData['text']) : '';
                // Уровень заголовка — часть структуры, берётся из русской версии.
            }

            return [
                'uid' => $block['uid'],
                'type' => $block['type'],
                'data' => $data,
            ];
        }, $primary);
    }

    private static function canonicalData(string $type, array $data): array
    {
        if ($type === 'text') {
            return ['html' => is_string($data['html'] ?? null) ? $data['html'] : ''];
        }

        // «HTML-код»: сохраняется как есть, без обрезки и очистки.
        if ($type === 'embed') {
            return ['html' => is_string($data['html'] ?? null) ? $data['html'] : ''];
        }

        if ($type === 'heading') {
            $level = ($data['level'] ?? 'h2') === 'h3' ? 'h3' : 'h2';

            return [
                'text' => is_string($data['text'] ?? null) ? trim($data['text']) : '',
                'level' => $level,
            ];
        }

        if ($type === 'image') {
            return ['path' => self::normalizePath($data['path'] ?? null)];
        }

        // Галереи: фиксированное число ячеек, пути по позициям.
        $cells = self::cells($type);
        $images = array_values((array) ($data['images'] ?? []));
        $images = array_pad(array_slice($images, 0, $cells), $cells, null);

        return ['images' => array_map([self::class, 'normalizePath'], $images)];
    }

    /**
     * Приводит путь картинки к относительному виду внутри `public/uploads/`.
     *
     * Проверяет форму пути, а не то, «каким инструментом он создан»: любой
     * безопасный `.webp` под `uploads/` сохраняется как есть. Так работают все
     * источники сразу — загрузчик панели (`2026/09/abc.webp`), сиды
     * (`seed/opp-xxx.webp`), демоконтент (`dev/news-1.webp`) и любой будущий —
     * без правки этого метода. Раньше здесь был жёсткий шаблон загрузчика, и
     * сохранение записи, залитой сидом, обнуляло её обложку.
     *
     * Отсекается: полный URL, абсолютный путь, выход из каталога (`..`),
     * не-webp, слишком глубокая вложенность.
     */
    public static function normalizePath(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        // Срезаем протокол/домен и любой ведущий `uploads/` (в т.ч. задвоенный).
        $value = preg_replace('#^https?://[^/]+#i', '', (string) $value);
        $value = ltrim($value, '/');
        $value = preg_replace('#^(uploads/)+#', '', $value);

        // 1–4 сегмента; каждый начинается с буквы/цифры (значит, сегмент не может
        // быть `.` или `..`), внутри — буквы, цифры, `._-`; расширение .webp.
        $segment = '[A-Za-z0-9][A-Za-z0-9._-]*';

        return preg_match("#^{$segment}(/{$segment}){0,3}\\.webp$#", $value) ? $value : null;
    }
}
