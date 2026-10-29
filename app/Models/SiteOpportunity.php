<?php

namespace App\Models;

use App\Enums\PublishStatus;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteOpportunity extends Model
{
    use HasTranslations, Publishable;

    /** Новая возможность видна и на сайте, и в кабинете: убрать одно из этого можно галочками в админке. */
    protected $attributes = [
        'show_on_site' => true,
        'show_in_cabinet' => true,
    ];

    protected $fillable = [
        'slug',
        'cover_path',
        'status',
        'published_at',
        'author',
        'deadline_at',
        'tag_id',
        'show_on_site',
        'show_in_cabinet',
    ];

    protected function casts(): array
    {
        return [
            'status' => PublishStatus::class,
            'published_at' => 'datetime',
            'deadline_at' => 'date',
            'show_on_site' => 'boolean',
            'show_in_cabinet' => 'boolean',
        ];
    }

    /** Возможности, открытые на публичном сайте (вместе с published()). */
    public function scopeOnSite(Builder $query): Builder
    {
        return $query->where('show_on_site', true);
    }

    /** Возможности, которые видят участницы в кабинете (вместе с published()). */
    public function scopeInCabinet(Builder $query): Builder
    {
        return $query->where('show_in_cabinet', true);
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
