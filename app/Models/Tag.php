<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Support\Contrast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tag extends Model
{
    use HasTranslations;

    protected $fillable = [
        'color',
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(SiteOpportunity::class);
    }

    /** Всего материалов с этим тегом. */
    public function usageCount(): int
    {
        return ($this->posts_count ?? $this->posts()->count())
            + ($this->opportunities_count ?? $this->opportunities()->count());
    }

    /** Цвет текста на плашке — из яркости фона, одной общей функцией. */
    public function textColor(): string
    {
        return Contrast::textOn($this->color);
    }
}
