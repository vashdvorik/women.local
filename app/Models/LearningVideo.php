<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Обучающее видео для кабинета участницы (раздел «Обучение»). Ведётся в админке («Кабинеты участниц → Обучающие видео»)
 * и не связано с видео открытого сайта (App\Models\Video). Участницы видят только включённые («показывать»).
 */
class LearningVideo extends Model
{
    use HasTranslations;

    protected $fillable = ['youtube_id', 'youtube_url', 'cover_path', 'position', 'is_published'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderBy('id');
    }

    /** Обложка: собственная либо миниатюра с YouTube. */
    public function coverUrl(): string
    {
        return $this->cover_path
            ? '/uploads/'.$this->cover_path
            : "https://i.ytimg.com/vi/{$this->youtube_id}/hqdefault.jpg";
    }

    /**
     * Адрес встроенного проигрывателя. Домен youtube-nocookie.com: YouTube не ставит посетительнице свои cookie, пока она не
     * нажала «Смотреть»; без рекомендаций чужих роликов в конце.
     */
    public function embedUrl(): string
    {
        return "https://www.youtube-nocookie.com/embed/{$this->youtube_id}?rel=0&modestbranding=1&playsinline=1&autoplay=1";
    }

    /**
     * Ручной порядок: приходит списком id в новом порядке (перетаскивание в админке). Видео, которых нет в списке, встают
     * следом в прежнем порядке.
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
    }
}
