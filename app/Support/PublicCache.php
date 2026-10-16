<?php

namespace App\Support;

use App\Models\Album;
use App\Models\AlbumTranslation;
use App\Models\Event;
use App\Models\EventTranslation;
use App\Models\Expert;
use App\Models\ExpertTranslation;
use App\Models\Post;
use App\Models\PostTranslation;
use App\Models\Project;
use App\Models\ProjectTranslation;
use App\Models\SiteOpportunity;
use App\Models\SiteOpportunityTranslation;
use App\Models\Tag;
use App\Models\TagTranslation;
use App\Models\Video;
use App\Models\VideoTranslation;
use Closure;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Кеш содержимого публичных страниц: новости, фотоальбомы, публикации, возможности, видео, проекты, эксперты и
 * превью на главной. Страница показывается из кеша, а не из базы: на сайте, где тексты правят раз в неделю, один и тот же
 * список читается из базы тысячу раз подряд.
 *
 * Хранилище отдельное (диск `public` в config/cache.php, файлы в storage/framework/cache/public): сброс после правки
 * в админке стирает только его и не трогает остальной кеш (подборы участниц, цены подписки). Файловое хранилище не
 * обращается к базе вовсе, в отличие от `database`, где сама проверка кеша — запрос.
 *
 * Сбрасывается целиком (ключей по страницам слишком много, а правок мало) в трёх случаях:
 *  - любое сохранение или удаление записей этих разделов через модель (watch(), вешается в AppServiceProvider);
 *  - любое изменение в админке (middleware FlushPublicCacheAfterAdminChange): ловит и то, что идёт мимо моделей,
 *    например сдвиг порядка карточек одним запросом;
 *  - `php artisan optimize:clear` и `php artisan public-cache:clear` (после выкладки кода, если поменялись модели).
 * Время жизни записи ограничено (TTL), чтобы любая незамеченная правка всё равно дошла до страницы.
 *
 * Кешируются только опубликованные материалы. Черновик (предпросмотр администратора) и несуществующий адрес в кеш
 * не попадают.
 */
final class PublicCache
{
    /** Имя хранилища в config/cache.php. */
    public const STORE = 'public';

    /** Сколько секунд запись живёт без сброса: запас на случай правки, которую никто не сообщил кешу. */
    public const TTL = 900;

    /** Дальше этой страницы списка кеш не заполняется: перебор номеров страниц ботами не должен засорять диск. */
    public const MAX_CACHED_PAGE = 20;

    /** Модели, любое изменение которых сбрасывает кеш. @var list<class-string> */
    private const WATCHED = [
        Event::class, EventTranslation::class,
        Expert::class, ExpertTranslation::class,
        Album::class, AlbumTranslation::class,
        Post::class, PostTranslation::class,
        SiteOpportunity::class, SiteOpportunityTranslation::class,
        Project::class, ProjectTranslation::class,
        Video::class, VideoTranslation::class,
        Tag::class, TagTranslation::class,
    ];

    /**
     * Значение из кеша или результат $callback, который тут же кладётся в кеш. Результат null не кладётся.
     * Адрес сайта входит в ключ: ссылки в карточках абсолютные, и страница по другому адресу (www, http, тестовый
     * домен) не должна получить чужие.
     */
    public static function remember(string $key, Closure $callback): mixed
    {
        if (! self::configured()) {
            return $callback();
        }

        return self::store()->remember(md5(url('/')).'.'.$key, self::TTL, $callback);
    }

    /** Стирает кеш публичных страниц. После коммита транзакции: иначе страница успела бы закешировать старые данные. */
    public static function flush(): void
    {
        if (self::configured()) {
            DB::afterCommit(static fn () => self::store()->flush());
        }
    }

    /** Следит за моделями публичных разделов: сохранили или удалили запись — кеш сброшен. */
    public static function watch(): void
    {
        foreach (self::WATCHED as $model) {
            $model::saved(static fn () => self::flush());
            $model::deleted(static fn () => self::flush());
        }
    }

    /**
     * Есть ли хранилище `public` в настройках. Нет его, пока после выкладки не сброшен старый закешированный конфиг
     * (между `git pull` и `artisan optimize:clear`, а также в самой команде optimize:clear на первой выкладке с ним):
     * тогда кеша просто нет, страницы читаются из базы, а не отвечают ошибкой.
     */
    private static function configured(): bool
    {
        return config('cache.stores.'.self::STORE) !== null;
    }

    private static function store(): Repository
    {
        return Cache::store(self::STORE);
    }
}
