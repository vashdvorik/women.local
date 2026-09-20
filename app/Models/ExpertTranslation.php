<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpertTranslation extends Model
{
    protected $fillable = [
        'expert_id',
        'locale',
        'name',
        'role',
        'specialization',
        'description',
        'looking_for',
        'can_offer',
        'tags',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
        ];
    }

    public function expert(): BelongsTo
    {
        return $this->belongsTo(Expert::class);
    }
}
