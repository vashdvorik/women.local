<x-layouts.admin :title="__('Подписка: :name', ['name' => $profile->full_name ?: __('участница')])">
    <x-slot:actions>
        <a href="{{ route('admin.subscriptions.index') }}" class="btn-secondary">{{ __('К списку') }}</a>
        <a href="{{ route('admin.profiles.show', $profile) }}" class="btn-secondary">{{ __('Профиль') }}</a>
    </x-slot:actions>

    <div class="form-column space-y-6">

        <div class="card space-y-3">
            <div class="flex flex-wrap items-center gap-3">
                <h2 class="text-section font-semibold">{{ $profile->full_name ?: __('Без имени') }}</h2>
                <span class="badge {{ $current->isPaid() ? 'badge--published' : 'badge--draft' }}">{{ $current->shortTitle() }}</span>
            </div>
            <dl class="grid gap-x-6 gap-y-1 text-ui sm:grid-cols-2">
                <div class="flex gap-2"><dt class="text-ink-muted">Telegram:</dt><dd>{{ $profile->telegram_username ? '@'.$profile->telegram_username : '—' }}</dd></div>
                <div class="flex gap-2"><dt class="text-ink-muted">{{ __('Статус профиля:') }}</dt><dd><x-admin.profile-status-badge :status="$profile->status" /></dd></div>
                <div class="flex gap-2"><dt class="text-ink-muted">{{ __('Тариф сейчас:') }}</dt><dd>{{ $current->title('ru') }}</dd></div>
                <div class="flex gap-2"><dt class="text-ink-muted">{{ __('Действует до:') }}</dt><dd>{{ $current->isPaid() ? $profile->plan_ends_at->format('d.m.Y H:i') : '—' }}</dd></div>
            </dl>
        </div>

        @if($profile->isApproved())
            <x-forms.error-summary />

            <form method="POST" action="{{ route('admin.subscriptions.gift', $profile) }}" class="card space-y-5">
                @csrf

                <div>
                    <h2 class="text-section font-semibold">{{ __('Подарить подписку') }}</h2>
                    <p class="field-hint mt-1">
                        {{ __('Тариф включится на год, участница ничего не получит: ни сообщения в Telegram, ни письма, просто откроется доступ. Если такой тариф уже действует, год добавится к его концу.') }}
                    </p>
                </div>

                <div class="space-y-1">
                    <label for="gift-plan" class="field-label">{{ __('Тариф') }}</label>
                    <select id="gift-plan" name="plan" class="field-input">
                        @foreach(\App\Enums\Plan::paid() as $plan)
                            <option value="{{ $plan->value }}" @selected(old('plan', 'community') === $plan->value)>
                                {{ $plan->title('ru') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <label for="gift-note" class="field-label">{{ __('Заметка (видна только администраторам)') }}</label>
                    <input id="gift-note" type="text" name="note" maxlength="200" value="{{ old('note') }}" placeholder="{{ __('Например: подарок за выступление на встрече') }}" class="field-input">
                </div>

                <button type="submit" class="btn-primary">{{ __('Подарить на год') }}</button>
            </form>

            <details class="card" @if($errors->any() && old('mode')) open @endif>
                <summary class="cursor-pointer text-section font-semibold">{{ __('Другой срок или точная дата') }}</summary>
            <form method="POST" action="{{ route('admin.subscriptions.grant', $profile) }}" class="mt-4 space-y-5"
                  x-data="{ mode: @js(old('mode', 'months')) }">
                @csrf

                <p class="field-hint">
                    {{ __('Для оплаты переводом и исправлений. Срок «на N месяцев» добавляется к уже оплаченному периоду того же тарифа. Точная дата заменяет все текущие периоды участницы одним. Более высокий тариф включается сразу.') }}
                </p>

                <div class="space-y-1">
                    <label for="plan" class="field-label">{{ __('Тариф') }}</label>
                    <select id="plan" name="plan" class="field-input">
                        @foreach(\App\Enums\Plan::paid() as $plan)
                            <option value="{{ $plan->value }}" @selected(old('plan', 'community') === $plan->value)>
                                {{ $plan->title() }} — {{ __(':price в год', ['price' => $plan->priceLabel()]) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <fieldset class="space-y-2">
                    <legend class="field-label mb-1">{{ __('Срок') }}</legend>
                    <label class="flex items-center gap-2 text-ui">
                        <input type="radio" name="mode" value="months" x-model="mode" class="border-hairline text-accent focus:ring-accent-soft">
                        <span>{{ __('На') }}</span>
                        <input type="number" name="months" min="1" max="60" value="{{ old('months', 12) }}" class="field-input !w-24" :disabled="mode !== 'months'">
                        <span>{{ __('мес.') }}</span>
                    </label>
                    <label class="flex items-center gap-2 text-ui">
                        <input type="radio" name="mode" value="until" x-model="mode" class="border-hairline text-accent focus:ring-accent-soft">
                        <span>{{ __('До даты') }}</span>
                        <input type="date" name="until" value="{{ old('until') }}" min="{{ now()->addDay()->format('Y-m-d') }}" class="field-input !w-auto" :disabled="mode !== 'until'">
                    </label>
                </fieldset>

                <div class="space-y-1">
                    <label for="note" class="field-label">{{ __('Заметка (видна только администраторам)') }}</label>
                    <input id="note" type="text" name="note" maxlength="200" value="{{ old('note') }}" placeholder="{{ __('Например: перевод на счёт 12.10, чек №…') }}" class="field-input">
                </div>

                <label class="flex items-center gap-2 text-ui">
                    <input type="checkbox" name="notify" value="1" class="rounded border-hairline text-accent focus:ring-accent-soft">
                    <span>{{ __('Сообщить участнице в Telegram (по умолчанию тариф включается молча)') }}</span>
                </label>

                <button type="submit" class="btn-secondary">{{ __('Выдать тариф') }}</button>
            </form>
            </details>

            @if($current->isPaid())
                <div class="card flex flex-wrap items-center justify-between gap-3">
                    <p class="text-ui text-ink-muted">{{ __('Закрыть платный доступ сейчас: участница сразу вернётся на Open. Деньги этим не возвращаются, возврат делается в банке.') }}</p>
                    <x-admin.confirm-button
                        :action="route('admin.subscriptions.revoke', $profile)"
                        :label="__('Закрыть доступ')"
                        :title="__('Вернуть на Open?')"
                        :message="__(':name потеряет каталог участниц, поиск и ИИ-помощника сразу.', ['name' => $profile->full_name ?: __('Участница')])"
                        :confirm="__('Закрыть доступ')"
                        trigger="btn-danger"
                        :danger="true" />
                </div>
            @endif
        @else
            <div class="card text-ui text-ink-muted">{{ __('Тариф выдаётся только одобренным участницам. Сначала одобрите профиль.') }}</div>
        @endif

        <div class="card space-y-3">
            <h2 class="text-section font-semibold">{{ __('История периодов') }}</h2>
            @if($history->isEmpty())
                <p class="text-ui text-ink-muted">{{ __('Платных периодов не было.') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead><tr><th>{{ __('Тариф') }}</th><th>{{ __('С') }}</th><th>{{ __('По') }}</th><th>{{ __('Откуда') }}</th><th>{{ __('Заметка') }}</th></tr></thead>
                        <tbody>
                            @foreach($history as $row)
                                <tr>
                                    <td class="whitespace-nowrap">{{ $row->planEnum()->shortTitle() }}</td>
                                    <td class="whitespace-nowrap text-ink-muted">{{ $row->starts_at->format('d.m.Y') }}</td>
                                    <td class="whitespace-nowrap text-ink-muted">{{ $row->ends_at->format('d.m.Y') }}</td>
                                    <td class="whitespace-nowrap">
                                        @if($row->payment)
                                            <a href="{{ route('admin.payments.show', $row->payment) }}" class="table-link">{{ $row->sourceLabel() }}</a>
                                        @else
                                            {{ $row->sourceLabel() }}
                                        @endif
                                    </td>
                                    <td class="text-ink-muted">{{ $row->note }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="card space-y-3">
            <h2 class="text-section font-semibold">{{ __('Платежи') }}</h2>
            @if($payments->isEmpty())
                <p class="text-ui text-ink-muted">{{ __('Платежей не было.') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead><tr><th>{{ __('Счёт') }}</th><th>{{ __('Тариф') }}</th><th>{{ __('Сумма') }}</th><th>{{ __('Статус') }}</th><th>{{ __('Дата') }}</th></tr></thead>
                        <tbody>
                            @foreach($payments as $payment)
                                <tr>
                                    <td class="whitespace-nowrap"><a href="{{ route('admin.payments.show', $payment) }}" class="table-link">{{ $payment->invoice_id }}</a></td>
                                    <td class="whitespace-nowrap">{{ $payment->planEnum()->shortTitle() }}</td>
                                    <td class="whitespace-nowrap">{{ number_format($payment->rubles(), 0, ',', ' ') }}</td>
                                    <td class="whitespace-nowrap">{{ $payment->statusLabel() }}@if($payment->is_test) <span class="badge badge--draft">{{ __('тест') }}</span>@endif</td>
                                    <td class="whitespace-nowrap text-ink-muted">{{ $payment->created_at->format('d.m.Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
