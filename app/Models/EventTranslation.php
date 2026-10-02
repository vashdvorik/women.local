<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventTranslation extends Model
{
    protected $fillable = [
        'event_id',
        'locale',
        'type',
        'date_label',
        'title',
        'description',
        'content',
    ];

    protected function casts(): array
    {
        return ['content' => 'array'];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
