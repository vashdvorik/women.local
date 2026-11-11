<?php

use App\Support\SchedulerHealth;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Подписки. На сервере нужна одна запись cron на каждую минуту: `artisan schedule:run` (см. DEPLOY.txt).
// Ничего не накладывается само на себя: при долгом запуске следующий пропускается. withoutOverlapping(N): блокировка сама
// снимается через N минут. На общем хостинге процесс могут убить посреди работы, и по умолчанию (24 часа) такая блокировка
// держала бы задачу сутки.
Schedule::command('payments:reconcile')->everyFiveMinutes()->withoutOverlapping(10);
Schedule::command('subscriptions:notify')->dailyAt('09:00')->withoutOverlapping(120);

// Регистрации без подтверждённой почты старше 30 дней удаляются: опечатка в адресе не должна занимать его навсегда.
Schedule::command('accounts:purge-unverified')->dailyAt('04:30')->withoutOverlapping(30);

// Резервная копия: база данных и все загруженные на сайт фото и файлы, на сервер и на Google Диск (rclone). Подключение: docs/BACKUP.md.
Schedule::command('backup:run')->dailyAt('03:30')->withoutOverlapping(240);

// «Пульс»: раз в минуту отмечаем, что cron жив. Админка по нему предупреждает, если cron на хостинге перестал работать.
Schedule::call(fn () => SchedulerHealth::beat())->everyMinute()->name('scheduler-heartbeat');

// Рассылки в Telegram: наступило время запланированной — запускаем; идущие ведём срезом ~45 с. Команда отправляет сама,
// поэтому рассылка уходит, даже если очередь (queue:work) на хостинге не запущена.
Schedule::command('broadcasts:dispatch-due')->everyMinute()->withoutOverlapping(5);
