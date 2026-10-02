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
     * Полный набор для публикаций и возможностей. `embed` — произвольный HTML,
     * который выводится на сайте без обработки (AGENTS.md §5.5); структурный,
     * то есть одинаковый для всех языков. `file` — PDF для скачивания (каталог,
     * брошюра): сам файл общий для всех языков, переводится только название.
     */
    public const ARTICLE_KINDS = ['text', 'heading', 'embed', 'file', 'image', 'gallery_2', 'gallery_3', 'gallery_4'];

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
            } elseif ($block['type'] === 'file') {
                $data['title'] = self::fileTitle($incomingData['title'] ?? null);
                // Сам файл (путь, имя, размер) — часть структуры, из русской версии.
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

        // «Файл (PDF)»: путь к файлу, название на странице, а также имя и размер
        // исходного файла — они нужны только редактору, чтобы показать, что загружено.
        if ($type === 'file') {
            $path = self::normalizeFilePath($data['path'] ?? null);

            return [
                'path' => $path,
                'title' => self::fileTitle($data['title'] ?? null),
                'name' => $path === null ? null : self::fileName($data['name'] ?? null),
                'size' => $path === null ? null : self::fileSize($data['size'] ?? null),
            ];
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
        return self::normalizeUploadPath($value, 'webp');
    }

    /**
     * То же для PDF-файла блока «Файл (PDF)»: безопасный `.pdf` под `uploads/`
     * (загрузчик кладёт их в `files/ГГГГ/ММ/`). Картинка `.webp` файлом не считается,
     * а PDF — картинкой, поэтому подменить один вид другим через JSON не получится.
     */
    public static function normalizeFilePath(mixed $value): ?string
    {
        return self::normalizeUploadPath($value, 'pdf');
    }

    /** Название файла на странице: одна строка, без лишних пробелов, не длиннее поля БД. */
    public static function fileTitle(mixed $value): string
    {
        if (! is_string($value)) {
            return '';
        }

        return mb_substr(trim(preg_replace('/\s+/u', ' ', $value) ?? ''), 0, 191);
    }

    /** Имя исходного файла для редактора: только последний сегмент, без управляющих символов. */
    public static function fileName(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $name = basename(str_replace('\\', '/', $value));
        $name = trim(preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?? '');

        return $name === '' ? null : mb_substr($name, 0, 191);
    }

    /** Размер файла в байтах; нечисловое и отрицательное — «неизвестно». */
    public static function fileSize(mixed $value): ?int
    {
        return is_numeric($value) && $value >= 0 ? (int) $value : null;
    }

    private static function normalizeUploadPath(mixed $value, string $extension): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        // Срезаем протокол/домен и любой ведущий `uploads/` (в т.ч. задвоенный).
        $value = preg_replace('#^https?://[^/]+#i', '', (string) $value);
        $value = ltrim($value, '/');
        $value = preg_replace('#^(uploads/)+#', '', $value);

        // 1–4 сегмента; каждый начинается с буквы/цифры (значит, сегмент не может
        // быть `.` или `..`), внутри — буквы, цифры, `._-`; расширение задано.
        $segment = '[A-Za-z0-9][A-Za-z0-9._-]*';

        return preg_match("#^{$segment}(/{$segment}){0,3}\\.{$extension}$#", $value) ? $value : null;
    }
}
