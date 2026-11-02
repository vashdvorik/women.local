<?php

namespace App\Services\Broadcasts;

/** Итог одной отправки в Telegram. */
final class DeliveryOutcome
{
    public const SENT = 'sent';
    /** Участница заблокировала бота, удалила аккаунт или чат не найден: повторять бессмысленно. */
    public const BLOCKED = 'blocked';
    public const FAILED = 'failed';
    /** Telegram просит подождать (429): сообщение ещё не отправлено. */
    public const RETRY = 'retry';
    /** Временный сбой (сеть, таймаут, 5xx Telegram): сообщение не отправлено, попробуем позже, до трёх раз. */
    public const TRANSIENT = 'transient';
    /** Telegram не принимает самого бота (неверный токен, 401/404): дальше слать бессмысленно, рассылка встаёт на паузу. */
    public const FATAL = 'fatal';

    private function __construct(
        public readonly string $status,
        public readonly ?string $error = null,
        public readonly int $retryAfter = 0,
    ) {}

    public static function sent(): self
    {
        return new self(self::SENT);
    }

    public static function blocked(string $error): self
    {
        return new self(self::BLOCKED, $error);
    }

    public static function failed(string $error): self
    {
        return new self(self::FAILED, mb_substr($error, 0, 500));
    }

    public static function transient(string $error): self
    {
        return new self(self::TRANSIENT, mb_substr($error, 0, 500));
    }

    public static function fatal(string $error): self
    {
        return new self(self::FATAL, mb_substr($error, 0, 500));
    }

    public static function retry(int $seconds): self
    {
        return new self(self::RETRY, null, max(1, $seconds));
    }

    public function isSent(): bool
    {
        return $this->status === self::SENT;
    }
}
