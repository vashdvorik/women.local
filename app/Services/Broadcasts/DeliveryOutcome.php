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

    public static function retry(int $seconds): self
    {
        return new self(self::RETRY, null, max(1, $seconds));
    }

    public function isSent(): bool
    {
        return $this->status === self::SENT;
    }
}
