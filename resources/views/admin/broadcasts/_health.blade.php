{{--
    Предупреждения о том, почему рассылка может не уходить. $schedulerStale / $schedulerLast — «пульс» планировщика (cron),
    $broadcast и $pausedFor — только на странице одной рассылки.
--}}
@if($schedulerStale)
    <div class="card border-l-4 border-l-red-500 space-y-1" role="alert">
        <p class="text-reading font-semibold text-red-700">{{ __('Планировщик (cron) на хостинге не запускается') }}</p>
        <p class="text-ui text-ink-muted">
            @if($schedulerLast)
                {{ __('Последний раз он отработал :time. Пока cron не работает, запланированные рассылки не уйдут, а идущие не продолжатся после паузы.', ['time' => $schedulerLast->setTimezone(\App\Models\Broadcast::TIMEZONE)->format('d.m.Y H:i')]) }}
            @else
                {{ __('Он ещё ни разу не отработал. Пока cron не работает, запланированные рассылки не уйдут, а идущие не продолжатся после паузы.') }}
            @endif
            {{ __('В cPanel → Cron Jobs должна быть запись раз в минуту: /usr/local/bin/ea-php83 /home/…/artisan schedule:run (см. DEPLOY.txt).') }}
        </p>
    </div>
@endif

@isset($broadcast)
    @if($broadcast->status === \App\Models\Broadcast::STATUS_SENDING && filled($broadcast->last_error))
        <div class="card border-l-4 border-l-amber-500 space-y-1" role="status">
            <p class="text-reading font-semibold text-amber-800">{{ __('Рассылка на паузе') }}</p>
            <p class="text-ui text-ink-muted">{{ $broadcast->last_error }}</p>
            @if(($pausedFor ?? 0) > 0)
                <p class="text-ui text-ink-muted">{{ __('Продолжится сама примерно через :minutes мин.', ['minutes' => max(1, (int) ceil($pausedFor / 60))]) }}</p>
            @endif
        </div>
    @elseif($broadcast->status === \App\Models\Broadcast::STATUS_SENDING && ($pausedFor ?? 0) > 0)
        <div class="card border-l-4 border-l-amber-500 space-y-1" role="status">
            <p class="text-reading font-semibold text-amber-800">{{ __('Рассылка ждёт: Telegram просит не отправлять слишком часто') }}</p>
            <p class="text-ui text-ink-muted">{{ __('Продолжится сама через :seconds с. Ничего делать не нужно.', ['seconds' => $pausedFor]) }}</p>
        </div>
    @endif
@endisset
