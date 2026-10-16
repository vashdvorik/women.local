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

    /**
     * Почему счёт нельзя удалить; null, если можно.
     *
     * Счёт — запись о деньгах, поэтому стирать можно только то, за что деньги не принимались: тестовый счёт
     * (банк при IsTest ничего не списывает) и боевой, по которому банк уже подтвердил, что оплаты нет
     * (не оплачен, отменён, истёк). Оплаченный, проверяемый и ещё живой боевой счёт остаются: по ним могли
     * принять деньги, а банк пришлёт оповещение об оплате на номер, которого у нас уже не будет.
     */
    public function deleteBlockedReason(): ?string
    {
        if ($this->is_test) {
            return null;
        }

        return match ($this->status) {
            self::STATUS_FAILED, self::STATUS_CANCELLED, self::STATUS_EXPIRED => null,
            self::STATUS_PAID => __('Оплаченный счёт удалить нельзя: это запись о принятых деньгах, по ней выдан тариф. Ошибочно выданный тариф снимают в карточке подписки, а деньги возвращают в кабинете банка.'),
            self::STATUS_VERIFYING => __('Счёт проверяется: банк мог принять деньги. Сначала нажмите «Проверить в банке» или подтвердите оплату вручную.'),
            default => __('Боевой счёт ещё может быть оплачен. Нажмите «Проверить в банке»: когда банк подтвердит, что оплаты нет (счёт станет «Истёк», «Отменён» или «Не оплачен»), его можно будет удалить.'),
        };
    }

    public function isDeletable(): bool
    {
        return $this->deleteBlockedReason() === null;
    }

    /** Что сказать администратору перед удалением (окно подтверждения). */
    public function deleteWarning(): string
    {
        $text = $this->is_test
            ? __('Это тестовый счёт: деньги по нему не списывались. Он исчезнет из списка без возможности восстановления.')
            : __('Банк подтвердил, что оплаты по этому счёту нет. Он исчезнет из списка без возможности восстановления.');

        if ($this->subscription !== null) {
            $text .= ' '.__('Тариф участницы останется: снять его можно в карточке подписки.');
        }

        return $text;
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
