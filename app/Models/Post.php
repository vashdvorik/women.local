<?php

namespace App\Models;

use App\Enums\PublishStatus;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    use HasTranslations, Publishable;

    /** Новость видна на открытом сайте; в кабинет («Возможности» и «Обучение») её добавляют галочками в админке. */
    protected $attributes = [
        'show_on_site' => true,
        'show_in_cabinet' => false,
        'show_in_learning' => false,
    ];

    protected $fillable = [
        'slug',
        'cover_path',
        'status',
        'published_at',
        'author',
        'tag_id',
        'show_on_site',
        'show_in_cabinet',
        'show_in_learning',
    ];

    protected function casts(): array
    {
        return [
            'status' => PublishStatus::class,
            'published_at' => 'datetime',
            'show_on_site' => 'boolean',
            'show_in_cabinet' => 'boolean',
            'show_in_learning' => 'boolean',
        ];
    }

    /** Новости, открытые на публичном сайте (вместе с published()). */
    public function scopeOnSite(Builder $query): Builder
    {
        return $query->where('show_on_site', true);
    }

    /** Новости в кабинете, в разделе «Возможности» (вместе с published()). */
    public function scopeInCabinet(Builder $query): Builder
    {
        return $query->where('show_in_cabinet', true);
    }

    /** Новости в кабинете, в разделе «Обучение» (вместе с published()). */
    public function scopeInLearning(Builder $query): Builder
    {
        return $query->where('show_in_learning', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }
}
