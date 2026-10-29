<x-layouts.admin :title="__('Рассылка')">
    <div class="space-y-4">
        <div class="card flex flex-wrap items-center justify-between gap-3">
            <p class="text-ui text-ink-muted max-w-2xl">
                {{ __('Сообщение участницам в Telegram (получат только те, кто подключил Telegram в профиле): текст на трёх языках, фото и кнопки-ссылки. Можно отправить сразу или в назначенное время, а отчёт покажет, кому оно дошло.') }}
            </p>
            <a href="{{ route('admin.broadcasts.create') }}" class="btn-primary">{{ __('Новая рассылка') }}</a>
        </div>

        @if($broadcasts->isEmpty())
            <div class="card text-center">
                <p class="text-reading text-ink-muted">{{ __('Рассылок пока не было.') }}</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('Сообщение') }}</th>
                            <th class="whitespace-nowrap">{{ __('Кому') }}</th>
                            <th class="whitespace-nowrap">{{ __('Статус') }}</th>
                            <th class="whitespace-nowrap">{{ __('Дошло') }}</th>
                            <th class="whitespace-nowrap">{{ __('Когда') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($broadcasts as $broadcast)
                            <tr>
                                <td class="max-w-md">
                                    <a href="{{ route('admin.broadcasts.show', $broadcast) }}" class="table-link">{{ \Illuminate\Support\Str::limit(\App\Support\BroadcastText::plain($broadcast->text('ru')), 90) }}</a>
                                </td>
                                <td class="text-ink-muted">{{ $broadcast->audienceLabel() }}</td>
                                <td class="whitespace-nowrap"><span class="badge {{ $broadcast->statusBadge() }}">{{ $broadcast->statusLabel() }}</span></td>
                                <td class="whitespace-nowrap text-ink-muted">
                                    @if($broadcast->total > 0)
                                        {{ $broadcast->sent }} / {{ $broadcast->total }}
                                        @if($broadcast->blocked + $broadcast->failed > 0)<span class="text-ink-faint">({{ __('не дошло: :count', ['count' => $broadcast->blocked + $broadcast->failed]) }})</span>@endif
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="whitespace-nowrap text-ink-muted">
                                    @if($broadcast->status === \App\Models\Broadcast::STATUS_SCHEDULED)
                                        {{ $broadcast->scheduledLocal()->format('d.m.Y H:i') }}
                                    @else
                                        {{ ($broadcast->started_at ?? $broadcast->created_at)->setTimezone(\App\Models\Broadcast::TIMEZONE)->format('d.m.Y H:i') }}
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-2">
                                        @if($broadcast->isEditable())
                                            <a href="{{ route('admin.broadcasts.edit', $broadcast) }}" class="btn-quiet">{{ __('Изменить') }}</a>
                                        @else
                                            <a href="{{ route('admin.broadcasts.show', $broadcast) }}" class="btn-quiet">{{ __('Отчёт') }}</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $broadcasts->links() }}
        @endif
    </div>
</x-layouts.admin>
