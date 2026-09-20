<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

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

    public function embedUrl(): string
    {
        return "https://www.youtube.com/embed/{$this->youtube_id}";
    }
}
