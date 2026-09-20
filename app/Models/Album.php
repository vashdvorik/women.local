<?php

namespace App\Models;

use App\Enums\PublishStatus;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    use HasTranslations, Publishable;

    protected $fillable = [
        'slug',
        'cover_path',
        'status',
        'published_at',
        'blocks',
    ];

    protected function casts(): array
    {
        return [
            'status' => PublishStatus::class,
            'published_at' => 'datetime',
            'blocks' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Реальное количество фотографий, суммированное по всем блокам. */
    public function photoCount(): int
    {
        return collect($this->blocks ?? [])
            ->flatMap(fn (array $block) => $block['data']['images'] ?? [])
            ->filter(fn ($path) => filled($path))
            ->count();
    }
}
