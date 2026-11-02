<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningVideoTranslation extends Model
{
    protected $fillable = ['learning_video_id', 'locale', 'title', 'description'];

    public function learningVideo(): BelongsTo
    {
        return $this->belongsTo(LearningVideo::class);
    }
}
