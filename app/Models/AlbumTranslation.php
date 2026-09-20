<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlbumTranslation extends Model
{
    protected $fillable = [
        'album_id',
        'locale',
        'title',
        'excerpt',
    ];

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }
}
