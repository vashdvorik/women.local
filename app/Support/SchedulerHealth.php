<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;

/**
 * «Пульс» планировщика: он раз в минуту записывает время в кеш (routes/console.php). Админка по нему видит, что cron на
 * хостинге перестал запускать `artisan schedule:run`: тогда не уходят запланированные рассылки, не сверяются платежи и не
 * приходят напоминания об окончании тарифа.
 */
final class SchedulerHealth
{
    public const KEY = 'scheduler-heartbeat';

    /** Через сколько минут без пульса считаем, что cron не работает (его запись минутная, запас на сбои хостинга). */
    private const STALE_MINUTES = 5;

    public static function beat(): void
    {
        Cache::put(self::KEY, now()->getTimestamp(), 86400 * 7);
    }

    public static function lastBeat(): ?CarbonInterface
    {
        $timestamp = Cache::get(self::KEY);

        return $timestamp === null ? null : now()->setTimestamp((int) $timestamp);
    }

    public static function isStale(): bool
    {
        $last = self::lastBeat();

        return $last === null || $last->lt(now()->subMinutes(self::STALE_MINUTES));
    }
}
