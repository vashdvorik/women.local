<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\ImageSource;
use App\Models\SiteSetting;
use App\Support\AspectRatio;
use App\Support\Blocks;
use App\Support\CardTone;
use App\Support\Locales;
use Carbon\Carbon;
use Database\Seeders\Concerns\ImportsThemeImages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

/**
 * Новости, подготовленные для внешнего сайта (посты организации): полный текст на трёх языках и фотографии.
 * Тексты лежат в database/seeders/data/news_archive/{номер}.php, фото — сжатые WebP в
 * public/themes/public/miro/images/news/archive/{номер}/ (их готовит `php artisan news:compress-archive`).
 *
 * На сервере достаточно одной команды, повторный запуск безопасен:
 *
 *   php artisan db:seed --class=NewsArchiveSeeder --force
 *
 * Что делает:
 *  - новую новость создаёт целиком: карточка, страница с текстом, обложка и галерея, на русском, румынском и английском;
 *  - три новости, которые уже есть на сайте карточкой со ссылкой («merge» в данных), дополняет: текст, фото и
 *    настоящая дата. Адрес страницы, заголовок, обложка и место в списке остаются прежними;
 *  - новые карточки ставит наверх списка по дате (старые сдвигаются вниз), а порядок потом меняется стрелками в админке;
 *  - каждое фото проходит тот же конвейер, что загрузка в админке: кадр по слоту → WebP со сжатием из настроек
 *    («Настройки сайта» → сжатие изображений); у портретных кадров окно сдвинуто к верху, чтобы не срезать лица.
 *    Сжатое фото целиком тоже сохраняется (диск originals), поэтому любой кадр потом меняется в админке кнопкой
 *    «Изменить кадр», не загружая фото заново;
 *  - запоминает, какие новости уже загружены (site_settings: news_archive): удалённую в админке новость повторный
 *    запуск не возвращает. А вот картинки, пропавшие с диска (папка uploads не в git), возвращает из исходников.
 *
 * Не перезаписывает ничего, что уже есть: у новости, где текст уже введён, текст не трогает. Готовые картинки
 * переделывает только отдельная команда `php artisan news:rebake-archive-photos` (см. rebakePhotos()).
 */
class NewsArchiveSeeder extends Seeder
{
    use ImportsThemeImages;

    /** Ключ настройки со списком загруженных новостей. */
    public const MARKER_KEY = 'news_archive';

    /** Подпись типа на карточке и цвет подложки. Один набор на все новости: так карточки выглядят единообразно. */
    private const TYPES = [
        'training' => ['ru' => 'Тренинг', 'en' => 'Training', 'ro' => 'Training', 'tone' => 'teal'],
        'conference' => ['ru' => 'Конференция', 'en' => 'Conference', 'ro' => 'Conferință', 'tone' => 'blue'],
        'forum' => ['ru' => 'Форум', 'en' => 'Forum', 'ro' => 'Forum', 'tone' => 'blue'],
        'networking' => ['ru' => 'Нетворкинг', 'en' => 'Networking', 'ro' => 'Networking', 'tone' => 'rose'],
        'award' => ['ru' => 'Премия', 'en' => 'Award', 'ro' => 'Premiu', 'tone' => 'orange'],
        'project' => ['ru' => 'Проект', 'en' => 'Project', 'ro' => 'Proiect', 'tone' => 'coral'],
        'event' => ['ru' => 'Мероприятие', 'en' => 'Event', 'ro' => 'Eveniment', 'tone' => 'pink'],
        'announcement' => ['ru' => 'Анонс', 'en' => 'Announcement', 'ro' => 'Anunț', 'tone' => 'coral'],
    ];

    /** Формат даты на карточке с годом: новости разных лет стоят в одном списке. */
    private const DATE_FORMATS = ['ru' => 'D MMMM YYYY', 'ro' => 'D MMMM YYYY', 'en' => 'MMMM D, YYYY'];

    /** Папка с исходниками фото внутри images/ темы. */
    private const PHOTOS_DIR = 'news/archive';

    /** Сколько знаков текста уходит во вступление до первого фото. */
    private const LEAD_CHARS = 250;

    /** Насколько окно кадра сдвинуто к верху, когда фото выше нужного (0 — верх, 0,5 — центр): на портретах лица сверху. */
    private const FOCUS_Y = 0.25;

    /** Расстояние между отпечатками снимков (из 64 бит), до которого снимки считаются одним кадром. */
    private const DUPLICATE_DISTANCE = 8;

    /** @var array<string, ?string> отпечатки снимков, уже посчитанные в этом процессе: «файл» → 64 бита */
    private static array $fingerprints = [];

    /** @var array<string, string> загруженные картинки за этот запуск: «файл|слот» → путь */
    private array $imported = [];

    /** @var list<string> заменённые при rebakePhotos() кадры, файлы которых надо удалить после записи новых */
    private array $stale = [];

    public function run(): void
    {
        $items = $this->items();

        if ($items === []) {
            $this->command?->warn('Данные новостей не найдены: database/seeders/data/news_archive.');

            return;
        }

        $done = $this->doneKeys();
        $created = $enriched = $restored = 0;

        DB::transaction(function () use ($items, &$done, &$created, &$enriched, &$restored) {
            $toCreate = [];

            foreach ($items as $item) {
                $event = $this->findEvent($item);

                if (in_array($item['key'], $done, true)) {
                    // Уже загружалась: удалённую в админке не возвращаем, пропавшие картинки восстанавливаем.
                    $restored += $event ? $this->restoreImages($event, $item) : 0;

                    continue;
                }

                if ($event === null) {
                    $toCreate[] = $item;
                } elseif ($item['merge'] !== null && ! $event->hasBody()) {
                    $this->enrich($event, $item);
                    $enriched++;
                } else {
                    $restored += $this->restoreImages($event, $item);
                }

                $done[] = $item['key'];
            }

            // Свежие новости — наверх списка: старые сдвигаются на их число, новые встают по дате (items уже по убыванию даты).
            if ($toCreate !== []) {
                Event::query()->increment('position', count($toCreate));
            }

            foreach ($toCreate as $index => $item) {
                $this->create($item, $index + 1);
                $created++;
            }

            SiteSetting::write(self::MARKER_KEY, ['keys' => array_values(array_unique($done))]);
        });

        $this->command?->info("Новости из архива: добавлено {$created}, дополнено {$enriched}, восстановлено картинок {$restored}.");
    }

    // ------------------------------------------------------------------ данные

    /**
     * Новости из файлов данных, от свежих к давним. Метод защищённый, чтобы тест мог взять одну-две новости
     * и проверить настоящую обработку фото, не гоняя все сто пять.
     *
     * @return list<array<string, mixed>>
     */
    protected function items(): array
    {
        $items = [];

        foreach (glob(database_path('seeders/data/news_archive/*.php')) ?: [] as $file) {
            /** @var array<string, mixed> $item */
            $item = require $file;
            $item['key'] = pathinfo($file, PATHINFO_FILENAME);
            $item['merge'] ??= null;
            $items[] = $item;
        }

        usort($items, fn (array $a, array $b) => [$b['date'], $b['key']] <=> [$a['date'], $a['key']]);

        return $items;
    }

    /** @return list<string> */
    private function doneKeys(): array
    {
        $stored = SiteSetting::read(self::MARKER_KEY, []);

        return is_array($stored) ? array_values(array_map('strval', (array) ($stored['keys'] ?? []))) : [];
    }

    /** Новость, которой соответствует запись данных: по русскому названию для «merge», иначе по адресу страницы. */
    private function findEvent(array $item): ?Event
    {
        $query = Event::query()->with('translations');

        if ($item['merge'] !== null) {
            $found = (clone $query)->whereHas('translations', fn ($q) => $q->where('locale', 'ru')->where('title', $item['merge']))->first();

            if ($found) {
                return $found;
            }
        }

        return $query->where('slug', $this->slug($item))->first();
    }

    private function slug(array $item): string
    {
        $base = trim(mb_substr(Str::slug($item['title']['ru'], '-', 'ru'), 0, 100), '-');

        return $base !== '' ? $base : 'news-'.$item['key'];
    }

    // ------------------------------------------------------------------ создание и дополнение

    private function create(array $item, int $position): Event
    {
        $plan = $this->plan($item);
        $type = self::TYPES[$item['type']] ?? self::TYPES['event'];

        // У «merge»-новости обычной обложки в плане нет (на сайте уже есть своя). Если же старой карточки не нашлось
        // и новость создаётся заново, обложкой становится лучший кадр, а те же фото остаются и на странице.
        $cover = $plan['cover'] ?? ($plan['photos'] !== [] ? $this->bestPhoto($plan['photos'], array_keys($plan['photos'])) : null);

        $event = Event::create([
            'slug' => $this->uniqueSlug($item),
            // У «merge»-новости обложка нужна только как запасная (когда старой карточки на сайте не нашлось).
            'image_path' => $cover !== null ? $this->importPhoto($plan['photos'][$cover], 'event') : null,
            'tone' => CardTone::normalize($type['tone']),
            'starts_at' => Carbon::parse($item['date'])->startOfDay(),
            'url' => null,
            'is_published' => true,
            'position' => $position,
        ]);

        $paths = $this->importBlockPhotos($plan);

        foreach (Locales::ALL as $locale) {
            $event->translations()->create([
                'locale' => $locale,
                'type' => $type[$locale],
                'date_label' => $this->dateLabel($item['date'], $locale),
                'title' => $item['title'][$locale],
                'description' => $item['description'][$locale],
                'content' => $this->blocks($item, $locale, $plan, $paths),
            ]);
        }

        return $event;
    }

    /** Старая карточка получает текст, фото и настоящую дату; всё остальное в ней не меняется. */
    private function enrich(Event $event, array $item): void
    {
        $plan = $this->plan($item);
        $paths = $this->importBlockPhotos($plan);

        $event->update(['starts_at' => Carbon::parse($item['date'])->startOfDay()]);

        foreach (Locales::ALL as $locale) {
            $translation = $event->translations->firstWhere('locale', $locale);

            if ($translation === null) {
                $type = self::TYPES[$item['type']] ?? self::TYPES['event'];
                $translation = $event->translations()->make([
                    'locale' => $locale,
                    'type' => $type[$locale],
                    'title' => $item['title'][$locale],
                    'description' => $item['description'][$locale],
                ]);
            }

            $translation->forceFill([
                // Прежняя подпись («20.05.2026») была условной датой переноса, теперь вместо неё настоящая.
                'date_label' => $this->dateLabel($item['date'], $locale),
                'content' => $this->blocks($item, $locale, $plan, $paths),
            ])->save();
        }
    }

    private function uniqueSlug(array $item): string
    {
        $base = $this->slug($item);
        $slug = $base;

        for ($i = 2; Event::query()->where('slug', $slug)->exists(); $i++) {
            $slug = $base.'-'.$i;
        }

        return $slug;
    }

    private function dateLabel(string $date, string $locale): string
    {
        return Carbon::parse($date)->locale($locale)->isoFormat(self::DATE_FORMATS[$locale]);
    }

    // ------------------------------------------------------------------ раскладка страницы

    /**
     * Что и где стоит на странице новости. Структура общая для всех языков: блоки, их порядок и фото одинаковы,
     * различается только текст.
     *
     * Обложка — самое «горизонтальное» и крупное фото. Остальные фото уходят на страницу: одно или два-три подряд
     * в галерею после вступления; если их четыре и больше, лучший кадр идёт крупной картинкой, остальные — галереями
     * после текста. У «merge»-новости обложка уже есть, поэтому на страницу идут все фото.
     *
     * @return array{photos: list<array{file: string, w: int, h: int}>, cover: ?int, blocks: list<array<string, mixed>>}
     */
    private function plan(array $item): array
    {
        $photos = [];

        foreach (glob(public_path('themes/public/miro/images/'.self::PHOTOS_DIR.'/'.$item['key'].'/*.webp')) ?: [] as $absolute) {
            [$w, $h] = getimagesize($absolute) ?: [0, 0];
            $photos[] = ['file' => self::PHOTOS_DIR.'/'.$item['key'].'/'.basename($absolute), 'w' => $w, 'h' => $h];
        }

        sort($photos);

        $all = array_keys($photos);
        $cover = ($item['merge'] === null && $all !== []) ? $this->bestPhoto($photos, $all) : null;
        // Обложка не повторяется на странице. У «merge»-новости обложка старая, и обычно это то же фото, что в посте:
        // такой снимок со страницы убираем.
        $skip = $cover === null ? $this->duplicatesOfOldCover($item, $photos) : [$cover];
        $onPage = array_values(array_diff($all, $skip));

        $paragraphs = $item['body']['ru'];
        $lead = $this->leadCount($paragraphs);
        $photoBlocks = $this->photoBlocks($photos, $onPage);

        $blocks = [];
        $add = function (array $block) use (&$blocks, $item): void {
            $block['uid'] = Uuid::uuid5(Uuid::NAMESPACE_URL, "women-hub-news-archive:{$item['key']}:".count($blocks))->toString();
            $blocks[] = $block;
        };

        $add(['type' => 'text', 'from' => 0, 'to' => $lead]);

        if ($photoBlocks !== []) {
            $add(array_shift($photoBlocks));
        }

        if ($lead < count($paragraphs)) {
            $add(['type' => 'text', 'from' => $lead, 'to' => count($paragraphs)]);
        }

        foreach ($photoBlocks as $block) {
            $add($block);
        }

        return ['photos' => $photos, 'cover' => $cover, 'blocks' => $blocks];
    }

    /**
     * Какие фото поста — тот же снимок, что и старая обложка карточки, которую дополняет «merge»-новость.
     * Снимки сравниваются по отпечатку (уменьшенная копия в оттенках серого): один и тот же кадр после разных
     * сжатий и размеров даёт расстояние около нуля, разные снимки — от двух десятков.
     *
     * @param  list<array{file: string, w: int, h: int}>  $photos
     * @return list<int>
     */
    private function duplicatesOfOldCover(array $item, array $photos): array
    {
        $theme = null;

        foreach ((array) (require database_path('seeders/data/events.php')) as $card) {
            if (($card['title']['ru'] ?? null) === $item['merge']) {
                $theme = public_path('themes/public/miro/images/'.ltrim((string) ($card['photo'] ?? ''), '/'));
            }
        }

        if ($theme === null || ! is_file($theme)) {
            return [];
        }

        $cover = $this->fingerprint($theme);
        $duplicates = [];

        foreach ($photos as $index => $photo) {
            $other = $this->fingerprint(public_path('themes/public/miro/images/'.$photo['file']));

            if ($cover !== null && $other !== null && $this->distance($cover, $other) <= self::DUPLICATE_DISTANCE) {
                $duplicates[] = $index;
            }
        }

        return $duplicates;
    }

    /** Отпечаток снимка: 64 бита «светлее ли пиксель правого соседа» на копии 9×8. null, если файл не открылся. */
    private function fingerprint(string $file): ?string
    {
        if (array_key_exists($file, self::$fingerprints)) {
            return self::$fingerprints[$file];
        }

        return self::$fingerprints[$file] = $this->computeFingerprint($file);
    }

    private function computeFingerprint(string $file): ?string
    {
        $image = @match (getimagesize($file)[2] ?? null) {
            IMAGETYPE_WEBP => imagecreatefromwebp($file),
            IMAGETYPE_JPEG => imagecreatefromjpeg($file),
            IMAGETYPE_PNG => imagecreatefrompng($file),
            default => false,
        };

        if ($image === false) {
            return null;
        }

        $small = imagecreatetruecolor(9, 8);
        imagecopyresampled($small, $image, 0, 0, 0, 0, 9, 8, imagesx($image), imagesy($image));

        $gray = function (int $x, int $y) use ($small): float {
            $rgb = imagecolorat($small, $x, $y);

            return (($rgb >> 16 & 255) * 299 + ($rgb >> 8 & 255) * 587 + ($rgb & 255) * 114) / 1000;
        };

        $bits = '';

        for ($y = 0; $y < 8; $y++) {
            for ($x = 0; $x < 8; $x++) {
                $bits .= $gray($x, $y) > $gray($x + 1, $y) ? '1' : '0';
            }
        }

        return $bits;
    }

    private function distance(string $a, string $b): int
    {
        return count(array_filter(str_split($a), fn (string $bit, int $i) => $bit !== $b[$i], ARRAY_FILTER_USE_BOTH));
    }

    /** Сколько первых абзацев образуют вступление: не меньше одного и не короче LEAD_CHARS знаков. */
    private function leadCount(array $paragraphs): int
    {
        $chars = 0;

        foreach ($paragraphs as $index => $paragraph) {
            $chars += mb_strlen($paragraph);

            if ($chars >= self::LEAD_CHARS) {
                return $index + 1;
            }
        }

        return max(1, count($paragraphs));
    }

    /**
     * @param  list<array{file: string, w: int, h: int}>  $photos
     * @param  list<int>  $indexes
     * @return list<array{type: string, files: list<int>}>
     */
    private function photoBlocks(array $photos, array $indexes): array
    {
        $count = count($indexes);

        if ($count === 0) {
            return [];
        }

        if ($count <= 3) {
            return [['type' => $count === 1 ? 'image' : "gallery_{$count}", 'files' => $indexes]];
        }

        $best = $this->bestPhoto($photos, $indexes);
        $rest = array_values(array_diff($indexes, [$best]));
        $blocks = [['type' => 'image', 'files' => [$best]]];

        foreach (array_chunk($rest, count($rest) === 4 ? 2 : 3) as $chunk) {
            $blocks[] = ['type' => count($chunk) === 1 ? 'image' : 'gallery_'.count($chunk), 'files' => $chunk];
        }

        return $blocks;
    }

    /**
     * Лучший кадр для широкой картинки: ближе к 16:9 и крупнее. Портрет такой кадр потеряет при обрезке, поэтому
     * горизонтальные снимки получают больше очков.
     *
     * @param  list<array{file: string, w: int, h: int}>  $photos
     * @param  list<int>  $indexes
     */
    private function bestPhoto(array $photos, array $indexes): int
    {
        $best = $indexes[0];
        $bestScore = -1.0;

        foreach ($indexes as $index) {
            $p = $photos[$index];
            $score = $p['h'] > 0 ? (min($p['w'] / $p['h'], 16 / 9) / (16 / 9)) * (min($p['w'], 1280) / 1280) : 0.0;

            if ($score > $bestScore) {
                $best = $index;
                $bestScore = $score;
            }
        }

        return $best;
    }

    /**
     * Блоки страницы на одном языке: тексты языка и общие для всех языков пути фото.
     *
     * @param  array<int, ?string>  $paths  путь загруженного фото по номеру в plan['photos']
     * @return list<array<string, mixed>>
     */
    private function blocks(array $item, string $locale, array $plan, array $paths): array
    {
        $paragraphs = $item['body'][$locale];
        $blocks = [];

        foreach ($plan['blocks'] as $block) {
            if ($block['type'] === 'text') {
                $html = $this->paragraphsHtml(
                    array_slice($paragraphs, $block['from'], $block['to'] - $block['from'], true),
                    $this->listFlags($item['body']['ru']),
                );

                $blocks[] = ['uid' => $block['uid'], 'type' => 'text', 'data' => ['html' => $html]];
            } elseif ($block['type'] === 'image') {
                $blocks[] = ['uid' => $block['uid'], 'type' => 'image', 'data' => ['path' => $paths[$block['files'][0]] ?? null]];
            } else {
                $blocks[] = ['uid' => $block['uid'], 'type' => $block['type'], 'data' => ['images' => array_map(fn (int $i) => $paths[$i] ?? null, $block['files'])]];
            }
        }

        return Blocks::canonical($blocks, Blocks::ARTICLE_KINDS);
    }

    /**
     * Какие абзацы — пункты списка: те, что в русском тексте начинаются с дефиса («-особенности …»). Список определяется
     * по русскому тексту, как и вся структура страницы, а на других языках лишь убирается дефис, если он есть.
     *
     * @param  list<string>  $russian
     * @return array<int, bool>
     */
    private function listFlags(array $russian): array
    {
        return array_map(fn (string $text) => (bool) preg_match('/^[-–•]/u', $text), $russian);
    }

    /**
     * Абзацы одного текстового блока в HTML: подряд идущие пункты собираются в маркированный список, остальное — абзацы.
     *
     * @param  array<int, string>  $paragraphs  абзацы блока по их номерам в тексте
     * @param  array<int, bool>  $isItem  номера абзацев-пунктов (по русскому тексту)
     */
    private function paragraphsHtml(array $paragraphs, array $isItem): string
    {
        $html = '';
        $open = false;

        foreach ($paragraphs as $index => $text) {
            $item = $isItem[$index] ?? false;

            if ($item && ! $open) {
                $html .= '<ul>';
                $open = true;
            } elseif (! $item && $open) {
                $html .= '</ul>';
                $open = false;
            }

            $html .= $item
                ? '<li>'.$this->html(trim(preg_replace('/^[-–•]\s*/u', '', $text) ?? $text)).'</li>'
                : '<p>'.$this->html($text).'</p>';
        }

        return $html.($open ? '</ul>' : '');
    }

    /** Абзац в HTML: текст экранируется, адреса сайтов становятся ссылками. */
    private function html(string $text): string
    {
        $escaped = e($text);

        return preg_replace_callback(
            '~https?://[^\s<]+[^\s<.,;:!?)]~u',
            fn (array $m) => '<a href="'.$m[0].'" target="_blank" rel="noopener">'.preg_replace('~^https?://~', '', $m[0]).'</a>',
            $escaped,
        ) ?? $escaped;
    }

    // ------------------------------------------------------------------ картинки

    /**
     * Загружает фото страницы по плану и возвращает пути.
     *
     * @return array<int, ?string>
     */
    private function importBlockPhotos(array $plan): array
    {
        $paths = [];

        foreach ($plan['blocks'] as $block) {
            if ($block['type'] === 'text') {
                continue;
            }

            foreach ($block['files'] as $index) {
                $paths[$index] = $this->importPhoto($plan['photos'][$index], $block['type']);
            }
        }

        return $paths;
    }

    /** @param  array{file: string, w: int, h: int}  $photo */
    private function importPhoto(array $photo, string $slot): ?string
    {
        $cacheKey = $photo['file'].'|'.$slot;

        if (isset($this->imported[$cacheKey]) && Storage::disk('uploads')->exists($this->imported[$cacheKey])) {
            return $this->imported[$cacheKey];
        }

        return $this->imported[$cacheKey] = $this->importThemeImage($photo['file'], $slot, $this->crop($photo, $slot));
    }

    /**
     * Прямоугольник, который вырезается из фото под слот. Шире нужного — режем по бокам от центра; выше нужного
     * (портреты) — окно сдвинуто к верху кадра.
     *
     * @param  array{file: string, w: int, h: int}  $photo
     * @return ?array{x: int, y: int, width: int, height: int}
     */
    private function crop(array $photo, string $slot): ?array
    {
        if ($photo['w'] <= 0 || $photo['h'] <= 0) {
            return null;
        }

        [$targetWidth, $targetHeight] = AspectRatio::dimensions($slot);
        $ratio = $targetWidth / $targetHeight;

        if ($photo['w'] / $photo['h'] >= $ratio) {
            $width = (int) round($photo['h'] * $ratio);

            return ['x' => intdiv($photo['w'] - $width, 2), 'y' => 0, 'width' => $width, 'height' => $photo['h']];
        }

        $height = (int) round($photo['w'] / $ratio);

        return ['x' => 0, 'y' => (int) round(($photo['h'] - $height) * self::FOCUS_Y), 'width' => $photo['w'], 'height' => $height];
    }

    /**
     * Переделывает фото уже загруженных новостей заново, из нынешних исходников темы и с нынешним сжатием.
     * Нужен, когда исходники или сжатие поменялись (так было, когда фото уходили на сайт пережатыми дважды):
     * обычный запуск сидера готовые картинки не трогает.
     *
     *   php artisan news:rebake-archive-photos
     *
     * Переделываются только картинки, которые сидер сделал сам и которых никто не менял: исходное фото кадра то же,
     * что в архиве, и рамка прежняя. Фото, замененное в админке, и кадр, перекадрированный кнопкой «Изменить кадр»,
     * остаются как есть. Прежние файлы кадров удаляются после того, как новые записаны.
     *
     * @return int сколько картинок переделано
     */
    public function rebakePhotos(): int
    {
        $done = $this->doneKeys();
        $count = 0;
        $this->stale = [];

        DB::transaction(function () use ($done, &$count) {
            foreach ($this->items() as $item) {
                if (! in_array($item['key'], $done, true)) {
                    continue;
                }

                $event = $this->findEvent($item);
                $count += $event ? $this->restoreImages($event, $item, rebake: true) : 0;
            }
        });

        $this->forgetStale();

        return $count;
    }

    /**
     * Была ли картинка сделана сидером из этого фото и осталась нетронутой: в таблице есть запись о ней, слот тот же,
     * сжатый оригинал на месте и это тот же снимок, что в архиве (по отпечатку), а рамка такая, какую выбрал бы сидер.
     *
     * @param  array{file: string, w: int, h: int}  $photo
     */
    private function isUntouchedArchiveFrame(?string $path, array $photo, string $slot): bool
    {
        $row = blank($path) ? null : ImageSource::query()->where('path', $path)->first();

        if ($row === null || $row->slot !== $slot || ! Storage::disk('originals')->exists($row->source_path)) {
            return false;
        }

        $expected = $this->crop(['file' => '', 'w' => $row->source_width, 'h' => $row->source_height], $slot);
        $rect = $row->cropRect();

        foreach (['x', 'y', 'width', 'height'] as $side) {
            if ($expected === null || abs($expected[$side] - $rect[$side]) > 1) {
                return false;
            }
        }

        $original = $this->fingerprint(Storage::disk('originals')->path($row->source_path));
        $archive = $this->fingerprint(public_path('themes/public/miro/images/'.$photo['file']));

        return $original !== null && $archive !== null && $this->distance($original, $archive) <= self::DUPLICATE_DISTANCE;
    }

    /** Старый кадр заменён: после записи новых путей его файл, запись о нём и ставший ненужным оригинал удаляются. */
    private function forgetStale(): void
    {
        foreach (array_unique($this->stale) as $path) {
            $sources = ImageSource::query()->where('path', $path)->pluck('source_path')->all();
            ImageSource::query()->where('path', $path)->delete();
            Storage::disk('uploads')->delete($path);

            foreach (array_unique($sources) as $source) {
                if (! ImageSource::query()->where('source_path', $source)->exists()) {
                    Storage::disk('originals')->delete($source);
                }
            }
        }

        $this->stale = [];
    }

    /**
     * Возвращает с диска пропавшие картинки новости: обложку (кроме «merge»-новостей: её восстанавливает EventSeeder)
     * и фото в блоках страницы. Если файл на месте или у записи нет картинки (убрана редактором), ничего не меняется.
     *
     * При $rebake картинки, что на месте, тоже делаются заново, но только нетронутые (см. rebakePhotos).
     *
     * @return int сколько картинок восстановлено
     */
    private function restoreImages(Event $event, array $item, bool $rebake = false): int
    {
        $plan = $this->plan($item);
        $restored = 0;

        $lost = fn (?string $path, array $photo, string $slot): bool => blank($path)
            ? false
            : (! Storage::disk('uploads')->exists($path) || ($rebake && $this->isUntouchedArchiveFrame($path, $photo, $slot)));

        if ($plan['cover'] !== null && $lost($event->image_path, $plan['photos'][$plan['cover']], 'event')) {
            $old = $event->image_path;
            $path = $this->importPhoto($plan['photos'][$plan['cover']], 'event');

            if ($path !== null) {
                $event->forceFill(['image_path' => $path])->save();
                if ($rebake) {
                    $this->stale[] = $old;
                }

                $restored++;
            }
        }

        $slots = [];
        foreach ($plan['blocks'] as $block) {
            if ($block['type'] !== 'text') {
                $slots[$block['uid']] = $block;
            }
        }

        $replace = [];  // старый путь → новый, один на все языки

        foreach ($event->translations as $translation) {
            $content = $translation->content;

            if (! is_array($content)) {
                continue;
            }

            $changed = false;

            foreach ($content as &$stored) {
                $block = $slots[$stored['uid'] ?? ''] ?? null;

                if ($block === null) {
                    continue;
                }

                $key = $block['type'] === 'image' ? 'path' : 'images';
                $current = $block['type'] === 'image' ? [$stored['data']['path'] ?? null] : array_values((array) ($stored['data']['images'] ?? []));

                foreach ($block['files'] as $position => $photoIndex) {
                    $path = $current[$position] ?? null;

                    if (! $lost($path, $plan['photos'][$photoIndex], $block['type'])) {
                        continue;
                    }

                    $replace[$path] ??= $this->importPhoto($plan['photos'][$photoIndex], $block['type']);

                    if ($replace[$path] === null) {
                        continue;
                    }

                    if ($rebake) {
                        $this->stale[] = $path;
                    }

                    $current[$position] = $replace[$path];
                    $changed = true;
                }

                $stored['data'][$key] = $block['type'] === 'image' ? ($current[0] ?? null) : $current;
            }
            unset($stored);

            if ($changed) {
                $translation->forceFill(['content' => $content])->save();
            }
        }

        return $restored + count(array_filter($replace));
    }
}
