<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Один клик по кнопке рассылки в Telegram (через ссылку-переходник). Кнопки нумеруются с нуля, как в рассылке. */
class BroadcastClick extends Model
{
    public $timestamps = false;

    protected $fillable = ['broadcast_id', 'broadcast_delivery_id', 'bot_user_id', 'button', 'clicked_at'];

    protected function casts(): array
    {
        return ['clicked_at' => 'datetime'];
    }

    public function broadcast(): BelongsTo
    {
        return $this->belongsTo(Broadcast::class);
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(BroadcastDelivery::class, 'broadcast_delivery_id');
    }
}
