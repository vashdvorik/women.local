<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteOpportunityTranslation extends Model
{
    protected $fillable = [
        'site_opportunity_id',
        'locale',
        'title',
        'excerpt',
        'content',
        'seo_title',
        'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
        ];
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(SiteOpportunity::class, 'site_opportunity_id');
    }
}
