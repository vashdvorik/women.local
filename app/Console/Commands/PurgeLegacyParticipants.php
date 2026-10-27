<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BotUser;
use App\Services\AvatarStore;
use Illuminate\Console\Command;

/**
 * Удаляет анкеты, созданные прежним способом (регистрация через Telegram-бота): у них нет почты, войти в них нельзя.
 * Нужна один раз при переходе на регистрацию по почте. Вместе с анкетой уходят её подписки, публикации и фото; платежи
 * остаются (у них пропадает только привязка к участнице).
 *
 *   php artisan participants:purge-legacy            — спросит подтверждение
 *   php artisan participants:purge-legacy --force    — без вопроса (для сервера)
 *   php artisan participants:purge-legacy --dry-run  — только показать, сколько анкет будет удалено
 */
class PurgeLegacyParticipants extends Command
{
    protected $signature = 'participants:purge-legacy {--force : Не спрашивать подтверждение} {--dry-run : Только посчитать, ничего не удалять}';

    protected $description = 'Удаляет анкеты без почты, созданные через Telegram-бота (прежняя регистрация)';

    public function handle(AvatarStore $avatars): int
    {
        $query = BotUser::query()->whereNull('email');
        $count = (clone $query)->count();

        if ($count === 0) {
            $this->info('Анкет без почты нет: удалять нечего.');

            return self::SUCCESS;
        }

        $this->line("Анкет без почты (создано через Telegram): {$count}.");

        if ($this->option('dry-run')) {
            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('Удалить их вместе с подписками, публикациями и фото? Это нельзя отменить.')) {
            $this->warn('Отменено, ничего не удалено.');

            return self::SUCCESS;
        }

        $deleted = 0;

        $query->orderBy('id')->each(function (BotUser $user) use ($avatars, &$deleted): void {
            $avatars->remove($user);
            $user->delete();
            $deleted++;
        });

        $this->info("Удалено анкет: {$deleted}.");

        return self::SUCCESS;
    }
}
