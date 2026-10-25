<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\SiteSetting;
use App\Support\AlbumWall;
use App\Support\AspectRatio;
use App\Support\Blocks;
use App\Support\Locales;
use App\Support\PublicCache;
use Database\Seeders\Concerns\ImportsThemeImages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;

/**
 * Проекты сайта (/projects и страницы /projects/{адрес}): тексты и фото присланы заказчиком (папка dizayn/ПРОЕКТЫ).
 * Данные — database/seeders/data/projects.php, сжатые фото — public/themes/public/miro/images/projects/{папка}/NN.webp.
 *
 * Что делает:
 *  - ОДИН РАЗ снимает с сайта 11 прежних проектов, которые заводил старый сидер (карточки без страницы, по названиям);
 *    вручную заведённые проекты не трогаются. Отметка об этом лежит в site_settings (projects_catalog);
 *  - заводит десять проектов: карточка (обложка 16:9, категория, название, короткий текст) и страница с рассказом блоками
 *    и фотографиями рядами (App\Support\AlbumWall) между абзацами, на трёх языках;
 *  - идемпотентен: проект с таким адресом (slug) уже есть — пропускается, правки в админке не затираются.
 *
 *   php artisan db:seed --class=ProjectSeeder
 */
class ProjectSeeder extends Seeder
{
    use ImportsThemeImages;

    public const MARKER_KEY = 'projects_catalog';

    private const PHOTOS_DIR = 'projects';

    /** Куда сдвигается окно кадра при вырезании горизонтальной плитки из вертикального снимка: к верху, чтобы не срезать лица. */
    private const FOCUS_Y = 0.25;

    /** Названия проектов, заведённых прежним сидером (карточки без страницы). */
    private const LEGACY_TITLES = [
        'Женское лидерство',
        'AdTrade: экспортный потенциал на берегах Днестра',
        'Tech Women Moldova',
        'Женское предпринимательство в производстве',
        'Женские инициативы в возобновляемой энергетике',
        'Женский бизнес-форум: рост, цифровизация, устойчивость',
        'Социальная интеграция через образование и самозанятость',
        'Экономическая устойчивость через энергоэффективность',
        'Поддержка и адаптация женщин-беженцев',
        'Digital Business Woman — Tiraspol',
        'Устойчивые сообщества: возможности для женщин',
    ];

    /** @var array<string, string> уже импортированные картинки: «файл|слот» → путь */
    private array $imported = [];

    public function run(): void
    {
        $removed = $this->removeLegacy();
        $created = 0;
        $skipped = 0;

        foreach ($this->items() as $index => $item) {
            if (Project::query()->where('slug', $item['slug'])->exists()) {
                $skipped++;

                continue;
            }

            DB::transaction(fn () => $this->create($item, $index + 1));
            $created++;
        }

        PublicCache::flush();

        $this->command?->info("Проекты: добавлено {$created}, уже были {$skipped}, прежних снято с сайта {$removed}.");
    }

    /** @return list<array<string, mixed>> */
    protected function items(): array
    {
        return (array) (function () {
            return require database_path('seeders/data/projects.php');
        })();
    }

    // ------------------------------------------------------------------ прежние проекты

    /** Снимает прежние проекты один раз; возвращает, сколько снято. */
    private function removeLegacy(): int
    {
        $marker = SiteSetting::read(self::MARKER_KEY, []);

        if (is_array($marker) && ! empty($marker['legacy_removed'])) {
            return 0;
        }

        $removed = 0;

        $legacy = Project::query()
            ->whereNull('slug')
            ->whereHas('translations', fn ($q) => $q->where('locale', Locales::PRIMARY)->whereIn('title', self::LEGACY_TITLES))
            ->get();

        foreach ($legacy as $project) {
            if (filled($project->image_path)) {
                Storage::disk('uploads')->delete($project->image_path);
            }

            $project->delete();
            $removed++;
        }

        SiteSetting::write(self::MARKER_KEY, ['legacy_removed' => true]);

        return $removed;
    }

    // ------------------------------------------------------------------ создание

    /** @param  array<string, mixed>  $item */
    private function create(array $item, int $position): void
    {
        $plan = $this->plan($item['folder']);

        $texts = $this->textBlocks($item['blocks']);
        $rows = [];
        $paths = [];

        foreach ($plan['rows'] as $row) {
            $images = [];

            foreach ($row['photos'] as $photoIndex) {
                $path = $this->importPhoto($plan['photos'][$photoIndex], $row['type']);

                if ($path !== null) {
                    $images[] = $path;
                }
            }

            if ($images !== []) {
                $rows[] = [
                    'uid' => $row['uid'],
                    'type' => $row['type'],
                    'data' => $row['type'] === 'image' ? ['path' => $images[0]] : ['images' => $images],
                ];
            }
        }

        $cover = $plan['cover'] !== null ? $this->importPhoto($plan['photos'][$plan['cover']], 'project') : null;

        $project = Project::create([
            'slug' => $item['slug'],
            'image_path' => $cover,
            'is_published' => true,
            'position' => $position,
        ]);

        // Структура и картинки — по русской версии; на других языках меняется только текст
        $primary = Blocks::canonical($this->weave($texts, $rows, 'ru'), Blocks::ARTICLE_KINDS);

        foreach (Locales::ALL as $locale) {
            $content = $locale === Locales::PRIMARY
                ? $primary
                : Blocks::mergeTranslation($primary, $this->weave($texts, $rows, $locale));

            $project->translations()->create([
                'locale' => $locale,
                'title' => $item['title'][$locale],
                'category' => $item['category'][$locale],
                'text' => $item['text'][$locale],
                'content' => $content,
            ]);
        }
    }

    /**
     * Текстовые блоки страницы со стабильными идентификаторами, общими для всех языков.
     *
     * @param  list<array<int, mixed>>  $blocks
     * @return list<array{uid: string, kind: string, ru: string, en: string, ro: string, level?: string}>
     */
    private function textBlocks(array $blocks): array
    {
        $out = [];

        foreach ($blocks as $i => $block) {
            $kind = $block[0];
            $uid = Uuid::uuid5(Uuid::NAMESPACE_URL, 'women-hub-projects:'.md5(json_encode($block)).':'.$i)->toString();

            if ($kind === 'ul') {
                $entry = ['uid' => $uid, 'kind' => 'text'];
                foreach (['ru' => 1, 'en' => 2, 'ro' => 3] as $locale => $at) {
                    $entry[$locale] = '<ul>'.collect($block[$at])->map(fn (string $li) => '<li>'.e($li).'</li>')->implode('').'</ul>';
                }
            } elseif ($kind === 'h') {
                $entry = ['uid' => $uid, 'kind' => 'heading', 'ru' => $block[1], 'en' => $block[2], 'ro' => $block[3], 'level' => 'h2'];
            } else {
                $entry = ['uid' => $uid, 'kind' => 'text', 'ru' => '<p>'.$block[1].'</p>', 'en' => '<p>'.$block[2].'</p>', 'ro' => '<p>'.$block[3].'</p>'];
            }

            $out[] = $entry;
        }

        return $out;
    }

    /**
     * Блоки страницы на одном языке: абзацы по порядку, ряды фото равномерно между ними. Ряд не ставится сразу после
     * подзаголовка (подзаголовок остаётся рядом со своим текстом).
     *
     * @param  list<array<string, mixed>>  $texts
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    private function weave(array $texts, array $rows, string $locale): array
    {
        $count = count($texts);
        $after = []; // сколько текстовых блоков стоит перед рядом

        foreach ($rows as $j => $row) {
            $at = (int) round(($j + 1) * $count / (count($rows) + 1));
            $at = max(1, min($count, $at));

            while ($at > 1 && ($texts[$at - 1]['kind'] ?? '') === 'heading') {
                $at--;
            }

            $after[$at][] = $row;
        }

        $result = [];

        foreach ($texts as $i => $text) {
            $result[] = $text['kind'] === 'heading'
                ? ['uid' => $text['uid'], 'type' => 'heading', 'data' => ['text' => $text[$locale], 'level' => $text['level']]]
                : ['uid' => $text['uid'], 'type' => 'text', 'data' => ['html' => $text[$locale]]];

            foreach ($after[$i + 1] ?? [] as $row) {
                $result[] = $row;
            }
        }

        return $result;
    }

    // ------------------------------------------------------------------ раскладка и картинки

    /**
     * Фото проекта по порядку имён, ряды (вид блока и номера фото) и фото для обложки.
     *
     * @return array{photos: list<array{file: string, w: int, h: int}>, rows: list<array{type: string, photos: list<int>, uid: string}>, cover: ?int}
     */
    private function plan(string $folder): array
    {
        $files = glob(public_path('themes/public/miro/images/'.self::PHOTOS_DIR.'/'.$folder.'/*.webp')) ?: [];
        usort($files, 'strnatcasecmp');

        $photos = [];

        foreach ($files as $absolute) {
            [$w, $h] = getimagesize($absolute) ?: [0, 0];
            $photos[] = ['file' => self::PHOTOS_DIR.'/'.$folder.'/'.basename($absolute), 'w' => $w, 'h' => $h];
        }

        $rows = [];

        foreach (AlbumWall::rows($photos) as $index => $row) {
            $row['uid'] = Uuid::uuid5(Uuid::NAMESPACE_URL, "women-hub-projects:{$folder}:row:{$index}")->toString();
            $rows[] = $row;
        }

        // Обложка 16:9: первый горизонтальный снимок; нет такого — первый вообще (кадр сдвинут к верху)
        $cover = null;

        foreach ($photos as $i => $photo) {
            if ($photo['w'] > $photo['h'] * 1.2) {
                $cover = $i;
                break;
            }
        }

        return ['photos' => $photos, 'rows' => $rows, 'cover' => $cover ?? ($photos === [] ? null : 0)];
    }

    /** @param  array{file: string, w: int, h: int}  $photo */
    private function importPhoto(array $photo, string $slot): ?string
    {
        $key = $photo['file'].'|'.$slot;

        if (isset($this->imported[$key]) && Storage::disk('uploads')->exists($this->imported[$key])) {
            return $this->imported[$key];
        }

        $path = $this->importThemeImage($photo['file'], $slot, $this->crop($photo, $slot));

        return $path === null ? null : $this->imported[$key] = $path;
    }

    /**
     * Прямоугольник, который вырезается из фото под слот: шире нужного — режем по бокам от центра; выше — окно сдвинуто к верху
     * кадра, чтобы не срезать лица.
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
}
