<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Plan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Один период подписки участницы. Текущий тариф считает App\Services\Subscriptions\SubscriptionService
 * и копирует в bot_users.plan / plan_ends_at.
 */
class Subscription extends Model
{
    /** Оплачена через банк. */
    public const SOURCE_PAYMENT = 'payment';

    /** Выдана или изменена администратором вручную. */
    public const SOURCE_ADMIN = 'admin';

    protected $fillable = [
        'bot_user_id',
        'plan',
        'starts_at',
        'ends_at',
        'source',
        'payment_id',
        'note',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function botUser(): BelongsTo
    {
        return $this->belongsTo(BotUser::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function planEnum(): Plan
    {
        return Plan::fromStored($this->plan);
    }

    /** Идёт прямо сейчас. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('starts_at', '<=', now())->where('ends_at', '>', now());
    }

    public function isActive(): bool
    {
        return $this->starts_at->lte(now()) && $this->ends_at->gt(now());
    }

    public function sourceLabel(): string
    {
        return $this->source === self::SOURCE_PAYMENT ? __('Оплата в банке') : __('Выдано вручную');
    }
}
