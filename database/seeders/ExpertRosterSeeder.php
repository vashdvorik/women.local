<?php

namespace Database\Seeders;

use App\Models\Expert;
use App\Models\SiteSetting;
use App\Support\PublicCache;
use Database\Seeders\Concerns\CreatesExperts;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Состав экспертов на сайте: дополнение к ExpertSeeder для базы, где эксперты уже заведены (ExpertSeeder там ничего
 * не добавляет). Перечень, тексты на трёх языках, фото и кадр — в database/seeders/data/experts.php.
 *
 * На сервере достаточно одной команды, повторный запуск безопасен:
 *
 *   php artisan db:seed --class=ExpertRosterSeeder --force
 *
 * Что делает:
 *  - заводит экспертов, которых ещё нет в базе (по имени по-русски), в конец списка, в порядке файла данных; фото
 *    проходит тот же конвейер, что загрузка в админке (кадр 1:1 → WebP), а у кого в файле задан 'crop' — по этому кадру;
 *  - у эксперта с ключом 'photo_rev' меняет фото на файл из данных (так на сайт попадают перерисованные портреты:
 *    правка файла в теме сама ничего не меняет, на сайт идёт готовый кадр из public/uploads). Один раз на пару «файл +
 *    номер версии»: фото, которое потом поменяли в админке, повторный запуск не трогает, пока в данных не поднимут
 *    номер версии или не сменят файл;
 *  - эксперта с ключом 'move_to_end' ставит на последнее место (после всех, в том числе только что добавленных) и
 *    нумерует список заново подряд. Тоже один раз на номер версии: порядок, который потом поменяли стрелками в
 *    админке, повторный запуск не трогает;
 *  - запоминает обработанных (site_settings: experts_roster): эксперта, удалённого в админке, повторный запуск не
 *    возвращает. Ни текст, ни порядок, ни цвет уже заведённого эксперта не перезаписываются.
 */
class ExpertRosterSeeder extends Seeder
{
    use CreatesExperts;

    /** Ключ настройки со списком обработанных экспертов и фото, которыми их заводили. */
    public const MARKER_KEY = 'experts_roster';

    public function run(): void
    {
        $profiles = $this->profiles();
        $marker = $this->marker();
        $created = 0;
        $replaced = 0;
        $moved = 0;

        DB::transaction(function () use ($profiles, &$marker, &$created, &$replaced, &$moved) {
            $position = (int) Expert::max('position');

            foreach ($profiles as $profile) {
                $name = $profile['name']['ru'];
                $expert = $this->findExpertByRuName($name);

                if ($expert === null) {
                    if (! in_array($name, $marker['names'], true)) {
                        $this->createExpert($profile, ++$position);
                        $created++;
                    }
                } else {
                    if (array_key_exists('photo_rev', $profile) && ($marker['photos'][$name] ?? null) !== self::photoKey($profile)) {
                        $path = $this->importThemeImage($profile['photo'] ?? null, 'expert', $profile['crop'] ?? null);

                        if ($path !== null) {
                            $expert->forceFill(['photo_path' => $path])->save();
                            $replaced++;
                        }
                    }

                    // Идёт по порядку файла, поэтому после тех, кого завели выше по списку в этом же запуске.
                    if (array_key_exists('move_to_end', $profile) && ($marker['moves'][$name] ?? null) !== (string) $profile['move_to_end']) {
                        $expert->forceFill(['position' => ++$position])->save();
                        $moved++;
                    }
                }

                $marker = self::merge($marker, $profile);
            }

            if ($moved > 0) {
                $this->renumberPositions();
            }

            SiteSetting::write(self::MARKER_KEY, $marker);
        });

        // Под DatabaseSeeder события моделей отключены, и кеш публичных страниц сам не сбросился бы.
        PublicCache::flush();

        $this->command?->info("Эксперты: добавлено {$created}, заменено фото {$replaced}, переставлено в конец {$moved}.");
    }

    /** После перестановки номера идут подряд (1…N) в том же порядке: на месте переставленного не остаётся дыры. */
    private function renumberPositions(): void
    {
        Expert::query()->ordered()->get()->each(function (Expert $expert, int $index) {
            if ($expert->position !== $index + 1) {
                $expert->forceFill(['position' => $index + 1])->save();
            }
        });
    }

    /**
     * Отмечает, что все записи файла данных уже применены: вызывает ExpertSeeder, когда заводит экспертов на пустой
     * базе, чтобы этот сидер не менял только что установленные фото второй раз.
     *
     * @param  list<array<string, mixed>>  $profiles
     */
    public static function remember(array $profiles): void
    {
        $marker = (new self)->marker();

        foreach ($profiles as $profile) {
            $marker = self::merge($marker, $profile);
        }

        SiteSetting::write(self::MARKER_KEY, $marker);
    }

    /**
     * Записи файла данных. Метод защищённый, чтобы тест мог подставить свои.
     *
     * @return list<array<string, mixed>>
     */
    protected function profiles(): array
    {
        return array_values((array) require database_path('seeders/data/experts.php'));
    }

    /**
     * @param  array{names: list<string>, photos: array<string, string>, moves: array<string, string>}  $marker
     * @param  array<string, mixed>  $profile
     * @return array{names: list<string>, photos: array<string, string>, moves: array<string, string>}
     */
    private static function merge(array $marker, array $profile): array
    {
        $name = $profile['name']['ru'];

        $marker['names'] = array_values(array_unique([...$marker['names'], $name]));

        if (filled($profile['photo'] ?? null)) {
            $marker['photos'][$name] = self::photoKey($profile);
        }

        if (array_key_exists('move_to_end', $profile)) {
            $marker['moves'][$name] = (string) $profile['move_to_end'];
        }

        return $marker;
    }

    /** Что запоминается о фото: «файл@версия» (версия без ключа 'photo_rev' — 1). @param  array<string, mixed>  $profile */
    private static function photoKey(array $profile): string
    {
        return ($profile['photo'] ?? '').'@'.($profile['photo_rev'] ?? 1);
    }

    /** @return array{names: list<string>, photos: array<string, string>, moves: array<string, string>} */
    private function marker(): array
    {
        $stored = SiteSetting::read(self::MARKER_KEY, []);
        $stored = is_array($stored) ? $stored : [];

        return [
            'names' => array_values(array_map('strval', (array) ($stored['names'] ?? []))),
            'photos' => array_map('strval', (array) ($stored['photos'] ?? [])),
            'moves' => array_map('strval', (array) ($stored['moves'] ?? [])),
        ];
    }
}
