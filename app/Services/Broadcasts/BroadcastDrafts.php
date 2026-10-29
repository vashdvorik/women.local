<?php

namespace App\Services\Broadcasts;

use App\Models\Broadcast;
use App\Models\Event;
use App\Models\Post;
use App\Models\SiteOpportunity;
use App\Support\BroadcastText;
use App\Support\Locales;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;
use Throwable;

/**
 * Черновик рассылки из новости, публикации или возможности (галочка «Создать черновик для рассылки» в их формах).
 * Текст — жирный заголовок и краткое описание на каждом языке, фото — обложка, кнопка «Подробнее» ведёт на страницу
 * материала. Ничего не отправляется: администратор открывает черновик в разделе «Рассылка», правит и отправляет сам.
 */
class BroadcastDrafts
{
    /** Подпись кнопки под сообщением на языке участницы. */
    private const BUTTON = ['ru' => 'Подробнее', 'en' => 'Read more', 'ro' => 'Citește mai mult'];

    /** Длина названия и описания вместе: с обложкой это подпись к фото, у неё в Telegram предел короче. */
    private const MAX_LENGTH = 900;

    /** Рассылки, уже созданные из этого материала (черновики и отправленные). */
    public function existing(Model $item): Collection
    {
        return Broadcast::query()->where('source_type', $item::class)->where('source_id', $item->getKey())->latest('id')->get();
    }

    /** Создаёт черновик; если из этого материала рассылка уже есть, второй не создаёт и возвращает null. */
    public function create(Model $item, string $audience, ?int $userId = null): ?Broadcast
    {
        if ($this->existing($item)->isNotEmpty()) {
            return null;
        }

        $item->loadMissing('translations');
        $texts = [];
        foreach (Locales::ALL as $locale) {
            $texts[$locale] = $this->text($item, $locale);
        }

        $url = $this->url($item);

        return Broadcast::create([
            'texts' => $texts,
            'buttons' => [[
                'url' => $url,
                'label' => self::BUTTON,
            ]],
            'audience' => in_array($audience, Broadcast::AUDIENCES, true) ? $audience : Broadcast::AUDIENCE_APPROVED,
            'photo_path' => $this->photo($this->cover($item)),
            'status' => Broadcast::STATUS_DRAFT,
            'created_by' => $userId,
            'source_type' => $item::class,
            'source_id' => $item->getKey(),
        ]);
    }

    /** «<b>Заголовок</b>» и описание; нет перевода на этом языке — пусто (при отправке подставится русский). */
    private function text(Model $item, string $locale): string
    {
        $translation = $item->translations->firstWhere('locale', $locale);
        $title = trim(strip_tags((string) $translation?->title));
        $description = trim(strip_tags((string) ($translation?->excerpt ?? $translation?->description)));

        if ($title === '') {
            return '';
        }

        $room = max(0, self::MAX_LENGTH - mb_strlen($title) - 2);
        $description = $description !== '' ? Str::limit($description, $room, '…') : '';

        return '<b>'.$title.'</b>'.($description !== '' ? "\n\n".$description : '');
    }

    private function url(Model $item): string
    {
        return match (true) {
            $item instanceof Event => $item->cardLink()['href'] ?? route('events'),
            $item instanceof Post => route('media.publications.show', $item),
            // Возможность только для участниц на открытом сайте не открывается: ссылка ведёт в кабинет (после входа)
            $item instanceof SiteOpportunity => $item->show_on_site
                ? route('opportunities.show', $item)
                : route('account.possibilities.show', $item->slug),
            default => url('/'),
        };
    }

    private function cover(Model $item): ?string
    {
        return $item instanceof Event ? $item->image_path : $item->cover_path;
    }

    /** Обложка → JPEG до 1600 px на закрытом диске (как фото, загруженное в форме рассылки). */
    private function photo(?string $coverPath): ?string
    {
        if (blank($coverPath) || ! Storage::disk('uploads')->exists($coverPath)) {
            return null;
        }

        try {
            $image = ImageManager::usingDriver(new Driver())->decode(Storage::disk('uploads')->get($coverPath))->scaleDown(width: 1600, height: 1600);
            $path = 'broadcasts/'.now()->format('Y/m').'/'.Str::lower(Str::random(16)).'.jpg';
            Storage::disk('originals')->put($path, (string) $image->encode(new JpegEncoder(quality: 85)));

            return $path;
        } catch (Throwable) {
            return null;   // не вышло с обложкой: черновик создаётся без фото, администратор добавит его сам
        }
    }
}
