<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CatalogTranslation extends Model
{
    protected $fillable = [
        'catalog_id',
        'locale',
        'title',
        'description',
    ];

    public function catalog(): BelongsTo
    {
        return $this->belongsTo(Catalog::class);
    }
}
