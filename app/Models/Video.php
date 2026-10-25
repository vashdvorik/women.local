<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Support\PublicCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Video extends Model
{
    use HasTranslations;

    protected $fillable = [
        'youtube_id',
        'youtube_url',
        'event_date',
        'cover_path',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
        ];
    }

    /** Обложка: собственная либо миниатюра с YouTube. */
    public function coverUrl(): string
    {
        return $this->cover_path
            ? '/uploads/'.$this->cover_path
            : "https://i.ytimg.com/vi/{$this->youtube_id}/hqdefault.jpg";
    }

    /**
     * Адрес встроенного проигрывателя (js/video-player.js открывает его поверх страницы). Домен youtube-nocookie.com:
     * YouTube не ставит посетителю свои cookie, пока тот не нажал «Смотреть». Без рекомендаций чужих роликов в конце.
     */
    public function embedUrl(): string
    {
        return "https://www.youtube-nocookie.com/embed/{$this->youtube_id}?rel=0&modestbranding=1&playsinline=1";
    }
    /**
     * Ручной порядок видео: приходит списком id в новом порядке (перетаскивание в админке). Видео, которых нет в списке,
     * встают следом в прежнем порядке.
     *
     * @param  list<int>  $ids
     */
    public static function applyOrder(array $ids): void
    {
        DB::transaction(function () use ($ids) {
            $ids = array_values(array_unique(array_map('intval', $ids)));
            $rest = static::query()->whereNotIn('id', $ids)->orderBy('position')->orderBy('id')->pluck('id')->all();

            foreach ([...$ids, ...$rest] as $index => $id) {
                static::query()->whereKey($id)->update(['position' => $index + 1]);
            }
        });

        PublicCache::flush();
    }
}
