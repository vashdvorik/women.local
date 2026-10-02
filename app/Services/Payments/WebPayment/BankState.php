<?php

declare(strict_types=1);

namespace App\Services\Payments\WebPayment;

/**
 * Состояние счёта в банке — разобранный ответ GetState (раздел 6.1 документа).
 *
 * state: 0 — не оплачен, 1 — оплачен, 2 — платёж отменён, 3 — ошибка платежа, 4 — платёж просрочен.
 * found = false, если банк ответил, что такого счёта нет или запрос не удался (Result/Code = 0).
 */
final class BankState
{
    public const NOT_PAID = 0;

    public const PAID = 1;

    public const CANCELLED = 2;

    public const ERROR = 3;

    public const EXPIRED = 4;

    /**
     * @param  array<string, string>  $raw  поля StateInfo как пришли от банка (для журнала и админки)
     */
    public function __construct(
        public readonly bool $found,
        public readonly ?int $state = null,
        public readonly ?int $sumKopecks = null,
        public readonly ?string $currency = null,
        public readonly ?bool $isTest = null,
        public readonly ?string $invoiceId = null,
        public readonly ?string $rrn = null,
        public readonly ?string $lastDigits = null,
        public readonly ?string $message = null,
        public readonly array $raw = [],
    ) {}

    public static function missing(?string $message = null): self
    {
        return new self(found: false, message: $message);
    }

    public function isPaid(): bool
    {
        return $this->found && $this->state === self::PAID;
    }

    /** Платёж закончился неудачей навсегда. */
    public function isDead(): bool
    {
        return $this->found && in_array($this->state, [self::CANCELLED, self::ERROR, self::EXPIRED], true);
    }
}
