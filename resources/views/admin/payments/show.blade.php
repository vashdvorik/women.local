<x-layouts.admin :title="__('Счёт :id', ['id' => $payment->invoice_id])">
    <x-slot:actions>
        <a href="{{ route('admin.payments.index') }}" class="btn-secondary">{{ __('К списку') }}</a>
        @if($payment->botUser)
            <a href="{{ route('admin.subscriptions.edit', $payment->botUser) }}" class="btn-secondary">{{ __('Подписка участницы') }}</a>
        @endif
    </x-slot:actions>

    <div class="form-column space-y-6">
        <div class="card space-y-4">
            <div class="flex flex-wrap items-center gap-3">
                <h2 class="text-section font-semibold">{{ $payment->invoice_id }}</h2>
                <span class="badge {{ $payment->isPaid() ? 'badge--published' : ($payment->status === 'verifying' ? 'badge--pending' : 'badge--draft') }}">{{ $payment->statusLabel() }}</span>
                @if($payment->is_test)<span class="badge badge--draft">{{ __('тест') }}</span>@endif
                @if($payment->driver === 'fake')<span class="badge badge--draft">{{ __('имитатор') }}</span>@endif
            </div>

            <dl class="grid gap-x-6 gap-y-1 text-ui sm:grid-cols-2">
                <div class="flex gap-2"><dt class="text-ink-muted">{{ __('Участница:') }}</dt><dd>{{ $payment->botUser?->full_name ?: __('удалена (Telegram ID :id)', ['id' => $payment->telegram_id]) }}</dd></div>
                <div class="flex gap-2"><dt class="text-ink-muted">{{ __('Тариф:') }}</dt><dd>{{ $payment->planEnum()->title('ru') }}, {{ __(':count мес.', ['count' => $payment->months]) }}</dd></div>
                <div class="flex gap-2"><dt class="text-ink-muted">{{ __('Сумма:') }}</dt><dd>{{ __(':sum (код валюты :currency; в банк ушло :kopecks коп.)', ['sum' => number_format($payment->rubles(), 2, ',', ' '), 'currency' => $payment->currency, 'kopecks' => $payment->amount]) }}</dd></div>
                <div class="flex gap-2"><dt class="text-ink-muted">{{ __('Создан:') }}</dt><dd>{{ $payment->created_at->format('d.m.Y H:i:s') }}</dd></div>
                <div class="flex gap-2"><dt class="text-ink-muted">{{ __('Счёт живёт до:') }}</dt><dd>{{ $payment->expires_at->format('d.m.Y H:i') }}</dd></div>
                <div class="flex gap-2"><dt class="text-ink-muted">{{ __('Оплачен:') }}</dt><dd>{{ $payment->paid_at?->format('d.m.Y H:i:s') ?? '—' }}</dd></div>
                <div class="flex gap-2"><dt class="text-ink-muted">RRN:</dt><dd>{{ $payment->rrn ?: '—' }}</dd></div>
                <div class="flex gap-2"><dt class="text-ink-muted">{{ __('Карта:') }}</dt><dd>{{ $payment->last_digits ? '…'.$payment->last_digits : '—' }}</dd></div>
                <div class="flex gap-2"><dt class="text-ink-muted">{{ __('Состояние в банке:') }}</dt><dd>{{ $payment->bank_state ?? '—' }} <span class="text-ink-faint">{{ __('(0 не оплачен, 1 оплачен, 2 отменён, 3 ошибка, 4 просрочен)') }}</span></dd></div>
                <div class="flex gap-2"><dt class="text-ink-muted">{{ __('Проверен:') }}</dt><dd>{{ $payment->checked_at?->format('d.m.Y H:i:s') ?? __('ещё не проверялся') }}</dd></div>
            </dl>

            @if(! empty($payment->payload['anomaly']))
                <div class="error-summary" role="alert">
                    <p class="error-summary__title">{{ __('Расхождение с банком') }}</p>
                    <p class="mt-1 text-ui">{{ $payment->payload['anomaly'] }}. {{ __('Тариф автоматически не включён. Проверьте платёж в кабинете банка: если деньги пришли на верную сумму, подтвердите вручную, если нет, верните деньги в банке.') }}</p>
                </div>
            @endif

            @if(! empty($payment->payload['last_error']))
                <p class="text-ui text-danger">{{ __('Последняя ошибка при обращении к банку:') }} {{ $payment->payload['last_error'] }}</p>
            @endif
        </div>

        @unless($payment->isPaid())
            <div class="card flex flex-wrap items-center gap-3">
                <form method="POST" action="{{ route('admin.payments.recheck', $payment) }}">
                    @csrf
                    <button type="submit" class="btn-secondary">{{ __('Проверить в банке') }}</button>
                </form>

                @if($payment->botUser)
                    <x-admin.confirm-button
                        :action="route('admin.payments.confirm', $payment)"
                        :label="__('Подтвердить вручную')"
                        :title="__('Подтвердить оплату вручную?')"
                        :message="__('Тариф :plan будет включён участнице на :months мес. без проверки банком. Делайте это, только если сами видели поступление денег.', ['plan' => $payment->planEnum()->shortTitle(), 'months' => $payment->months])"
                        :confirm="__('Подтвердить и выдать тариф')"
                        trigger="btn-quiet" />
                @endif
            </div>
        @endunless

        <div class="card space-y-3">
            <h2 class="text-section font-semibold">{{ __('Данные от банка') }}</h2>
            <pre class="max-h-96 overflow-auto rounded-md bg-surface-sunken p-3 text-caption">{{ json_encode($payment->payload ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
            <p class="field-hint">{{ __('Подпись и токен рекуррентных платежей здесь не хранятся.') }}</p>
        </div>
    </div>
</x-layouts.admin>
