@php
    $labels = [
        '' => __('Все'),
        \App\Models\Payment::STATUS_PAID => __('Оплачены'),
        \App\Models\Payment::STATUS_PENDING => __('Ждут оплаты'),
        \App\Models\Payment::STATUS_VERIFYING => __('Проверяются'),
        \App\Models\Payment::STATUS_FAILED => __('Не оплачены'),
        \App\Models\Payment::STATUS_CANCELLED => __('Отменены'),
        \App\Models\Payment::STATUS_EXPIRED => __('Истекли'),
    ];
@endphp

<x-layouts.admin :title="__('Платежи')">
    <div class="space-y-4">
        <div class="card space-y-1 text-ui text-ink-muted">
            <p>
                {{ __('Счета Web-платежа Агропромбанка за подписку. Тариф включается, когда банк сообщил об оплате и сам подтвердил её (запрос GetState). Если оповещение потерялось, счёт проверяется автоматически раз в 5 минут. Статус «Проверяется» дольше 15 минут означает расхождение с банком: откройте счёт и решите, что делать.') }}
            </p>
            <p>{{ __('Режим:') }} <b class="text-ink">{{ config('webpayment.driver') === 'bank' ? __('настоящий банк') : __('имитатор банка (разработка)') }}</b>,
                {{ config('webpayment.is_test') ? __('тестовые платежи (деньги не списываются)') : __('боевые платежи') }}.</p>
        </div>

        <div class="tabs overflow-x-auto no-scrollbar">
            @foreach($labels as $key => $label)
                <a href="{{ route('admin.payments.index', array_filter(['status' => $key, 'q' => $search])) }}"
                   class="tab whitespace-nowrap @if($status === (string) $key) tab--active @endif">
                    {{ $label }}<span class="text-ink-faint">{{ $key === '' ? $counts->sum() : ($counts[$key] ?? 0) }}</span>
                </a>
            @endforeach
        </div>

        <form method="GET" class="flex w-full max-w-sm gap-2">
            @if($status !== '') <input type="hidden" name="status" value="{{ $status }}"> @endif
            <input type="search" name="q" value="{{ $search }}" placeholder="{{ __('Номер счёта, RRN или имя') }}" class="field-input">
            <button type="submit" class="btn-secondary">{{ __('Найти') }}</button>
        </form>

        @if($payments->isEmpty())
            <div class="card text-center"><p class="text-reading text-ink-muted">{{ ($search !== '' || $status !== '') ? __('Ничего не найдено.') : __('Платежей пока нет.') }}</p></div>
        @else
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr><th>{{ __('Счёт') }}</th><th>{{ __('Участница') }}</th><th>{{ __('Тариф') }}</th><th>{{ __('Сумма') }}</th><th>{{ __('Статус') }}</th><th>{{ __('Создан') }}</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                            <tr>
                                <td class="whitespace-nowrap"><a href="{{ route('admin.payments.show', $payment) }}" class="table-link">{{ $payment->invoice_id }}</a></td>
                                <td>{{ $payment->botUser?->full_name ?: ('Telegram ID '.$payment->telegram_id) }}</td>
                                <td class="whitespace-nowrap">{{ $payment->planEnum()->shortTitle() }}</td>
                                <td class="whitespace-nowrap">{{ number_format($payment->rubles(), 0, ',', ' ') }}</td>
                                <td class="whitespace-nowrap">
                                    <span class="badge {{ $payment->isPaid() ? 'badge--published' : ($payment->status === 'verifying' ? 'badge--pending' : 'badge--draft') }}">{{ $payment->statusLabel() }}</span>
                                    @if($payment->is_test)<span class="badge badge--draft">{{ __('тест') }}</span>@endif
                                    @if($payment->driver === 'fake')<span class="badge badge--draft">{{ __('имитатор') }}</span>@endif
                                </td>
                                <td class="whitespace-nowrap text-ink-muted">{{ $payment->created_at->format('d.m.Y H:i') }}</td>
                                <td class="text-right"><a href="{{ route('admin.payments.show', $payment) }}" class="btn-quiet">{{ __('Открыть') }}</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $payments->links() }}
        @endif
    </div>
</x-layouts.admin>
