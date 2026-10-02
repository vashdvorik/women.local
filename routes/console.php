<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Подписки. На сервере нужна одна запись cron на каждую минуту: `artisan schedule:run` (см. DEPLOY.txt).
// Ничего не накладывается само на себя: при долгом запуске следующий пропускается.
Schedule::command('payments:reconcile')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('subscriptions:notify')->dailyAt('09:00')->withoutOverlapping();
