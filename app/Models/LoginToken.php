<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginToken extends Model
{
    protected $fillable = [
        'telegram_id',
        'token',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'telegram_id' => 'integer',
        'expires_at'  => 'datetime',
        'used_at'     => 'datetime',
    ];

    public function botUser(): BelongsTo
    {
        return $this->belongsTo(BotUser::class, 'telegram_id', 'telegram_id');
    }

    public function isValid(): bool
    {
        return $this->expires_at->isFuture();
    }

    /**
     * Generate a reusable 24-hour login token for the given Telegram user.
     * Deletes any previously issued tokens for that user first.
     */
    public static function generateFor(int $telegramId): self
    {
        self::where('telegram_id', $telegramId)->delete();

        return self::create([
            'telegram_id' => $telegramId,
            'token'       => bin2hex(random_bytes(32)),
            'expires_at'  => now()->addDay(),
        ]);
    }
}
