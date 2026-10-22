<x-layouts.admin :title="__('Обращения')">
    <div class="space-y-4">
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <input type="search" name="q" value="{{ $search }}" placeholder="{{ __('Поиск по имени, почте или тексту') }}"
                   class="field-input max-w-sm">
            <label class="flex items-center gap-2 text-reading">
                <input type="checkbox" name="unread" value="1" @checked($onlyUnread)>
                <span>{{ __('Только непрочитанные') }}</span>
            </label>
            <button type="submit" class="btn-secondary">{{ __('Найти') }}</button>
        </form>

        @if($messages->isEmpty())
            <div class="card text-center">
                <p class="text-reading text-ink-muted">
                    {{ $search !== '' || $onlyUnread ? __('Ничего не найдено.') : __('Обращений пока нет.') }}
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="w-28">{{ __('Статус') }}</th>
                            <th class="w-40">{{ __('Получено') }}</th>
                            <th class="w-56">{{ __('Имя') }}</th>
                            <th>{{ __('Сообщение') }}</th>
                            <th class="w-24"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($messages as $message)
                            <tr>
                                <td>
                                    @if($message->isRead())
                                        <span class="badge badge--draft">{{ __('Прочитано') }}</span>
                                    @else
                                        <span class="badge badge--new">{{ __('Новое') }}</span>
                                    @endif
                                    @if($message->mail_sent_at === null)
                                        <span class="badge badge--rejected mt-1" title="{{ __('Письмо на почту команды не ушло, обращение сохранено только здесь.') }}">{{ __('Без письма') }}</span>
                                    @endif
                                </td>
                                <td class="text-ink-muted">{{ $message->created_at->setTimezone(\App\Models\Broadcast::TIMEZONE)->format('d.m.Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.contact-messages.show', $message) }}" class="table-link @unless($message->isRead()) font-semibold @endunless">{{ $message->name }}</a>
                                    <div class="text-ink-muted text-sm">{{ $message->email }}</div>
                                    @if($message->organization)
                                        <div class="text-ink-muted text-sm">{{ $message->organization }}</div>
                                    @endif
                                </td>
                                <td class="text-ink-muted">{{ \Illuminate\Support\Str::limit(preg_replace('/\s+/u', ' ', $message->message), 140) }}</td>
                                <td class="text-right">
                                    <x-admin.delete-button
                                        :action="route('admin.contact-messages.destroy', $message)"
                                        :subject="$message->name"
                                        :noun="__('обращение')" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $messages->links() }}
        @endif
    </div>
</x-layouts.admin>
