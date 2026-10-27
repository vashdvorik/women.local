<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Plan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Заявка участницы на закрытый тариф («Оставить заявку» вместо «Купить»). Менеджер звонит, а потом открывает
 * кнопку «Купить» этой участнице лично (status = unlocked). Покупка по такой заявке закрывает её (purchased).
 */
class PlanRequest extends Model
{
    public const STATUS_NEW = 'new';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_UNLOCKED = 'unlocked';
    public const STATUS_PURCHASED = 'purchased';
    public const STATUS_DECLINED = 'declined';

    /** Заявки, которые ещё в работе: участнице нельзя оставить вторую по тому же тарифу. */
    public const OPEN_STATUSES = [self::STATUS_NEW, self::STATUS_CONTACTED, self::STATUS_UNLOCKED];

    protected $fillable = ['bot_user_id', 'plan', 'status', 'comment', 'manager_note'];

    protected $casts = [
        'contacted_at' => 'datetime',
        'unlocked_at' => 'datetime',
        'purchased_at' => 'datetime',
    ];

    public function botUser(): BelongsTo
    {
        return $this->belongsTo(BotUser::class);
    }

    public function planEnum(): Plan
    {
        return Plan::fromStored($this->plan);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', self::OPEN_STATUSES);
    }

    public function isUnlocked(): bool
    {
        return $this->status === self::STATUS_UNLOCKED;
    }

    /** @return list<string> */
    public static function statuses(): array
    {
        return [self::STATUS_NEW, self::STATUS_CONTACTED, self::STATUS_UNLOCKED, self::STATUS_PURCHASED, self::STATUS_DECLINED];
    }
}
