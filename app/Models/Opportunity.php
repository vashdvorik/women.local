<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Opportunity extends Model
{
    protected $fillable = [
        'bot_user_id',
        'type',
        'title',
        'body',
        'event_date',
        'location',
        'contact_url',
    ];

    protected $casts = [
        'event_date' => 'date',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(BotUser::class, 'bot_user_id');
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'project' => __('account.types.project'),
            'meeting' => __('account.types.meeting'),
            'event' => __('account.types.event'),
            default => $this->type,
        };
    }

    public function typeEmoji(): string
    {
        return match ($this->type) {
            'project' => '💼',
            'meeting' => '🤝',
            'event' => '📅',
            default => '📌',
        };
    }
}
