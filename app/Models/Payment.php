<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Plan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Счёт на оплату подписки в Web-платеже Агропромбанка. Подробности и жизненный цикл —
 * docs/SUBSCRIPTIONS.md; статусы сверяются с банком через App\Services\Payments\WebPayment\PaymentReconciler.
 */
class Payment extends Model
{
    public const STATUS_PENDING = 'pending';

    /** Банк прислал «оплачено», но мы ещё не подтвердили это запросом GetState. */
    public const STATUS_VERIFYING = 'verifying';

    public const STATUS_PAID = 'paid';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_EXPIRED = 'expired';

    /** Статусы, после которых счёт больше не меняется. */
    public const FINAL_STATUSES = [self::STATUS_PAID, self::STATUS_FAILED, self::STATUS_CANCELLED, self::STATUS_EXPIRED];

    protected $fillable = [
        'bot_user_id',
        'telegram_id',
        'plan',
        'months',
        'amount',
        'currency',
        'invoice_id',
        'status',
        'is_test',
        'driver',
        'expires_at',
        'paid_at',
        'rrn',
        'last_digits',
        'bank_state',
        'payload',
        'checked_at',
    ];

    protected $casts = [
        'telegram_id' => 'integer',
        'months' => 'integer',
        'amount' => 'integer',
        'is_test' => 'boolean',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
        'checked_at' => 'datetime',
        'bank_state' => 'integer',
        'payload' => 'array',
    ];

    public function botUser(): BelongsTo
    {
        return $this->belongsTo(BotUser::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    public function planEnum(): Plan
    {
        return Plan::fromStored($this->plan);
    }

    public function isFinal(): bool
    {
        return in_array($this->status, self::FINAL_STATUSES, true);
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /** Счёт ещё может быть оплачен: ждём банк или проверку. */
    public function isOpen(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_VERIFYING], true);
    }

    /** Сумма в рублях (в базе — копейки). */
    public function rubles(): float
    {
        return $this->amount / 100;
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_VERIFYING]);
    }

    /** Подпись статуса для админки. */
    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => __('Ждёт оплаты'),
            self::STATUS_VERIFYING => __('Проверяется в банке'),
            self::STATUS_PAID => __('Оплачен'),
            self::STATUS_FAILED => __('Не оплачен'),
            self::STATUS_CANCELLED => __('Отменён'),
            self::STATUS_EXPIRED => __('Истёк'),
            default => $this->status,
        };
    }
}
