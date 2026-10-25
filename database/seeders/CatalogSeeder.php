<?php

namespace Database\Seeders;

use App\Models\Catalog;
use App\Models\SiteSetting;
use App\Support\Locales;
use App\Support\PublicCache;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Журналы организации как каталоги с перелистыванием («Медиатека → Публикации»). Перечень, названия и описания на трёх
 * языках — в database/seeders/data/catalogs.php, страницы-картинки и PDF — в database/seeders/data/catalogs/{папка}/.
 *
 * На сервере достаточно одной команды, повторный запуск безопасен:
 *
 *   php artisan db:seed --class=CatalogSeeder --force
 *
 * Что делает:
 *  - копирует страницы (001.webp, 002.webp, …) и PDF каталога на диск uploads, как это делает форма в админке, и заводит
 *    опубликованный каталог с кнопкой «Скачать PDF»; обложка — первая страница;
 *  - каталоги идут в начало очереди в порядке файла данных (ранее заведённые остаются ниже);
 *  - запоминает заведённые каталоги (site_settings: catalogs_seed): каталог, удалённый в админке, повторный запуск не
 *    возвращает; каталог с тем же адресом не дублируется и не меняется, правки из админки не затираются.
 */
class CatalogSeeder extends Seeder
{
    /** Ключ настройки со списком заведённых каталогов (адреса). */
    public const MARKER_KEY = 'catalogs_seed';

    public function run(): void
    {
        $items = $this->items();
        $done = $this->doneKeys();
        $created = 0;
        $fresh = [];

        foreach ($items as $item) {
            $slug = $item['slug'];

            if (! in_array($slug, $done, true) && ! Catalog::where('slug', $slug)->exists()) {
                $catalog = $this->create($item);

                if ($catalog !== null) {
                    $fresh[] = $catalog;
                    $created++;
                }
            }

            $done[] = $slug;
        }

        DB::transaction(function () use ($fresh, $done) {
            // Новые каталоги встают в начало очереди, остальные сдвигаются ниже
            if ($fresh !== []) {
                Catalog::query()->increment('position', count($fresh));

                foreach ($fresh as $index => $catalog) {
                    $catalog->update(['position' => $index + 1]);
                }
            }

            SiteSetting::write(self::MARKER_KEY, ['keys' => array_values(array_unique($done))]);
        });

        // Под DatabaseSeeder события моделей отключены, и кеш публичных страниц сам не сбросился бы.
        PublicCache::flush();

        $this->command?->info("Каталоги: добавлено {$created}.");
    }

    /** Каталоги из файла данных. Метод защищённый, чтобы тест мог взять один каталог. @return list<array<string, mixed>> */
    protected function items(): array
    {
        return array_values((array) require database_path('seeders/data/catalogs.php'));
    }

    /** Папка с файлами каталога. Защищённый метод: тест подставляет свою маленькую папку. */
    protected function folder(string $name): string
    {
        return database_path('seeders/data/catalogs/'.$name);
    }

    private function create(array $item): ?Catalog
    {
        $folder = $this->folder($item['folder']);
        $files = File::isDirectory($folder) ? collect(File::files($folder))->filter(fn ($f) => $f->getExtension() === 'webp')->sortBy(fn ($f) => $f->getFilename())->values() : collect();

        if ($files->isEmpty()) {
            $this->command?->warn("Каталог {$item['slug']}: нет страниц в {$folder}, пропущен.");

            return null;
        }

        $disk = Storage::disk('uploads');
        $month = now()->format('Y/m');
        $paths = [];

        foreach ($files as $file) {
            $path = "catalogs/{$month}/".Str::lower(Str::random(14)).'.webp';
            $disk->put($path, File::get($file->getPathname()));
            $paths[] = $path;
        }

        $pdf = null;
        $pdfSource = $folder.'/catalog.pdf';

        if (File::exists($pdfSource)) {
            $pdf = 'catalogs/pdf/'.Str::lower(Str::random(14)).'.pdf';
            $disk->put($pdf, File::get($pdfSource));
        }

        return DB::transaction(function () use ($item, $paths, $pdf) {
            $catalog = Catalog::create([
                'slug' => $item['slug'],
                'pages' => $paths,
                'page_ratio' => $item['ratio'] ?: 0.7071,
                'pdf_path' => $pdf,
                'allow_download' => $pdf !== null,
                'is_published' => true,
                'position' => 0,
            ]);

            foreach (Locales::ALL as $locale) {
                $catalog->translations()->create([
                    'locale' => $locale,
                    'title' => $item['title'][$locale],
                    'description' => $item['description'][$locale] ?? null,
                ]);
            }

            return $catalog;
        });
    }

    /** @return list<string> */
    private function doneKeys(): array
    {
        $stored = SiteSetting::read(self::MARKER_KEY, []);

        return is_array($stored) ? array_values(array_map('strval', (array) ($stored['keys'] ?? []))) : [];
    }
}
