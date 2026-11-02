<?php

namespace App\Console\Commands;

use App\Models\BotUser;
use Illuminate\Console\Command;

/**
 * Раз в сутки (schedule:run): удаляет «мёртвые» регистрации, чтобы чужая опечатка не занимала адрес навсегда. Трогает только
 * тех, кто за 30 дней так и не подтвердил почту: анкеты нет, тарифа и платежей нет, Telegram и Google не подключены.
 */
class PurgeUnverifiedAccounts extends Command
{
    protected $signature = 'accounts:purge-unverified {--days=30 : Сколько дней регистрация может оставаться неподтверждённой} {--dry-run : Только показать, сколько будет удалено}';

    protected $description = 'Удаляет регистрации, у которых почта не подтверждена дольше срока (по умолчанию 30 дней)';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));

        $query = BotUser::query()
            ->whereNull('email_verified_at')
            ->whereNull('profile_completed_at')
            ->whereNull('google_id')
            ->whereNull('telegram_id')
            ->where(fn ($q) => $q->whereNull('plan')->orWhere('plan', '')->orWhere('plan', 'none'))
            ->whereDoesntHave('payments')
            ->where('created_at', '<', now()->subDays($days));

        $count = (clone $query)->count();

        if ($this->option('dry-run')) {
            $this->info("Будет удалено регистраций: {$count}.");

            return self::SUCCESS;
        }

        // Удаляем по одной, чтобы сработали связанные записи; регистраций такого рода мало.
        $query->get()->each(fn (BotUser $user) => $user->delete());

        if ($count > 0) {
            $this->info("Удалено неподтверждённых регистраций: {$count}.");
        }

        return self::SUCCESS;
    }
}
