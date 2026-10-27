<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Одноразовая ссылка подключения Telegram: t.me/<бот>?start=link_<token>. Бот по токену находит участницу
 * и записывает ей telegram_id для уведомлений. Действует час; новая ссылка отменяет прежние.
 */
class TelegramLink extends Model
{
    public const PREFIX = 'link_';

    protected $fillable = ['bot_user_id', 'token', 'expires_at'];

    protected $casts = ['expires_at' => 'datetime'];

    public function botUser(): BelongsTo
    {
        return $this->belongsTo(BotUser::class);
    }

    public static function issueFor(BotUser $user): self
    {
        self::where('bot_user_id', $user->id)->delete();

        return self::create([
            'bot_user_id' => $user->id,
            'token' => bin2hex(random_bytes(16)),
            'expires_at' => now()->addHour(),
        ]);
    }

    /** Ссылка для кнопки «Подключить Telegram». */
    public function url(): string
    {
        return rtrim((string) config('site.bot_url', 'https://t.me/WomenComBot'), '/').'?start='.self::PREFIX.$this->token;
    }

    public function isValid(): bool
    {
        return $this->expires_at->isFuture();
    }
}
