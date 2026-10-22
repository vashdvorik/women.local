@php
    $locales = ['ru' => __('Русский'), 'en' => 'English', 'ro' => 'Română'];
    $tabs = [
        '' => __('Все'),
        \App\Models\BroadcastDelivery::SENT => __('Дошло'),
        \App\Models\BroadcastDelivery::BLOCKED => __('Заблокировали бота'),
        \App\Models\BroadcastDelivery::FAILED => __('Ошибка'),
        \App\Models\BroadcastDelivery::PENDING => __('Ещё не отправлено'),
    ];
    $done = $broadcast->sent + $broadcast->failed + $broadcast->blocked;
    $percent = $broadcast->total > 0 ? (int) round($done / $broadcast->total * 100) : 0;
@endphp

<x-layouts.admin :title="__('Рассылка')">
    <div class="space-y-4">
        <div class="card space-y-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="space-y-1">
                    <span class="badge {{ $broadcast->statusBadge() }}">{{ $broadcast->statusLabel() }}</span>
                    <p class="text-ui text-ink-muted">
                        {{ __('Кому') }}: {{ $broadcast->audienceLabel() }}
                        @if($broadcast->status === \App\Models\Broadcast::STATUS_SCHEDULED)
                            · {{ __('отправка :time (по Кишинёву)', ['time' => $broadcast->scheduledLocal()->format('d.m.Y H:i')]) }}
                        @elseif($broadcast->started_at)
                            · {{ __('начата :time', ['time' => $broadcast->started_at->setTimezone(\App\Models\Broadcast::TIMEZONE)->format('d.m.Y H:i')]) }}
                        @endif
                        @if($broadcast->finished_at && $broadcast->status === \App\Models\Broadcast::STATUS_SENT)
                            · {{ __('закончена :time', ['time' => $broadcast->finished_at->setTimezone(\App\Models\Broadcast::TIMEZONE)->format('H:i')]) }}
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    @if($broadcast->isEditable())
                        <a href="{{ route('admin.broadcasts.edit', $broadcast) }}" class="btn-secondary">{{ __('Изменить') }}</a>
                    @endif
                    @if($broadcast->isActive())
                        <form method="POST" action="{{ route('admin.broadcasts.cancel', $broadcast) }}" onsubmit="return confirm(@js(__('Отменить рассылку? Тем, кому сообщение уже отправлено, его не вернуть.')))">
                            @csrf
                            <button type="submit" class="btn-danger">{{ __('Отменить') }}</button>
                        </form>
                    @elseif($broadcast->status !== \App\Models\Broadcast::STATUS_SENDING)
                        <form method="POST" action="{{ route('admin.broadcasts.destroy', $broadcast) }}" onsubmit="return confirm(@js(__('Удалить рассылку и её отчёт?')))">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-quiet">{{ __('Удалить') }}</button>
                        </form>
                    @endif
                </div>
            </div>

            @if($broadcast->total > 0)
                <div class="space-y-2">
                    <div class="h-2 w-full overflow-hidden rounded-full bg-hairline">
                        <div class="h-full bg-accent" style="width: {{ $percent }}%"></div>
                    </div>
                    <p class="text-ui text-ink-muted">
                        {{ __('Дошло') }}: <b class="text-ink">{{ $broadcast->sent }}</b> {{ __('из') }} {{ $broadcast->total }}
                        · {{ __('заблокировали бота') }}: {{ $broadcast->blocked }}
                        · {{ __('ошибок') }}: {{ $broadcast->failed }}
                        @if($broadcast->pending() > 0) · {{ __('осталось') }}: {{ $broadcast->pending() }} @endif
                    </p>
                    <p class="field-hint">{{ __('«Заблокировали бота» — участница остановила бота или удалила аккаунт Telegram: ей сообщение прийти не может.') }}</p>
                </div>
            @elseif($broadcast->isActive())
                <p class="text-ui text-ink-muted">{{ __('Получательниц в группе сейчас: :count.', ['count' => $audienceCount]) }}</p>
            @endif
        </div>

        {{-- Что отправляется --}}
        <div class="card space-y-4">
            <h2 class="text-section font-semibold">{{ __('Сообщение') }}</h2>
            @if($broadcast->hasPhoto())<p class="text-ui text-ink-muted">{{ __('С фото.') }}</p>@endif
            @foreach($locales as $code => $label)
                @continue(trim((string) ($broadcast->texts[$code] ?? '')) === '')
                <div class="space-y-1">
                    <p class="field-label">{{ $label }}</p>
                    <p class="whitespace-pre-line text-reading">{{ \App\Support\BroadcastText::plain($broadcast->texts[$code]) }}</p>
                    @foreach($broadcast->buttonsFor($code) as $button)
                        <p class="text-ui"><span class="badge badge--draft">{{ $button['text'] }}</span> <span class="text-ink-muted break-all">{{ $button['url'] }}</span></p>
                    @endforeach
                </div>
            @endforeach
        </div>

        @if($broadcast->total > 0)
            <div class="tabs overflow-x-auto no-scrollbar">
                @foreach($tabs as $key => $label)
                    <a href="{{ route('admin.broadcasts.show', array_filter(['broadcast' => $broadcast, 'filter' => $key])) }}"
                       class="tab whitespace-nowrap @if($filter === (string) $key) tab--active @endif">{{ $label }}</a>
                @endforeach
            </div>

            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('Участница') }}</th>
                            <th class="whitespace-nowrap">Username</th>
                            <th class="whitespace-nowrap">{{ __('Язык') }}</th>
                            <th class="whitespace-nowrap">{{ __('Итог') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deliveries as $delivery)
                            <tr>
                                <td>
                                    @if($delivery->botUser)
                                        <a href="{{ route('admin.profiles.show', $delivery->botUser) }}" class="table-link">{{ $delivery->botUser->full_name ?: __('Без имени') }}</a>
                                    @else
                                        <span class="text-ink-muted">{{ __('Профиль удалён') }}</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap text-ink-muted">{{ $delivery->botUser?->telegram_username ? '@'.$delivery->botUser->telegram_username : '—' }}</td>
                                <td class="whitespace-nowrap text-ink-muted">{{ strtoupper($delivery->locale) }}</td>
                                <td>
                                    @switch($delivery->status)
                                        @case(\App\Models\BroadcastDelivery::SENT)<span class="badge badge--published">{{ __('Дошло') }}</span>@break
                                        @case(\App\Models\BroadcastDelivery::BLOCKED)<span class="badge badge--pending">{{ __('Заблокировала бота') }}</span>@break
                                        @case(\App\Models\BroadcastDelivery::FAILED)<span class="badge badge--rejected">{{ __('Ошибка') }}</span>@break
                                        @default<span class="badge badge--draft">{{ __('Ожидает') }}</span>
                                    @endswitch
                                    @if($delivery->error)<span class="text-ui text-ink-muted"> {{ $delivery->error }}</span>@endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-ink-muted">{{ __('Ничего не найдено.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $deliveries->links() }}
        @endif
    </div>

    @if($broadcast->status === \App\Models\Broadcast::STATUS_SENDING)
        {{-- Идёт отправка: отчёт обновляется сам. --}}
        <script>setTimeout(function () { location.reload(); }, 5000);</script>
    @endif
</x-layouts.admin>
