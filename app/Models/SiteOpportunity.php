<?php

namespace App\Models;

use App\Enums\PublishStatus;
use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteOpportunity extends Model
{
    use HasTranslations, Publishable;

    protected $fillable = [
        'slug',
        'cover_path',
        'status',
        'published_at',
        'author',
        'deadline_at',
        'tag_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => PublishStatus::class,
            'published_at' => 'datetime',
            'deadline_at' => 'date',
        ];
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
