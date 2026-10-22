<x-layouts.admin :title="__('Обращение')">
    <x-slot:actions>
        <a href="{{ route('admin.contact-messages.index') }}" class="btn-secondary">{{ __('К списку') }}</a>
        <a href="mailto:{{ $contact->email }}" class="btn-primary">{{ __('Ответить письмом') }}</a>
    </x-slot:actions>

    <div class="space-y-4 max-w-3xl">
        <div class="card space-y-3">
            <dl class="grid grid-cols-[10rem_1fr] gap-x-4 gap-y-2 text-reading">
                <dt class="text-ink-muted">{{ __('Имя') }}</dt>
                <dd>{{ $contact->name }}</dd>

                <dt class="text-ink-muted">{{ __('Почта') }}</dt>
                <dd><a href="mailto:{{ $contact->email }}" class="table-link">{{ $contact->email }}</a></dd>

                <dt class="text-ink-muted">{{ __('Телефон') }}</dt>
                <dd>
                    @if($contact->phone)
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $contact->phone) }}" class="table-link">{{ $contact->phone }}</a>
                    @else
                        <span class="text-ink-muted">{{ __('Не указан') }}</span>
                    @endif
                </dd>

                <dt class="text-ink-muted">{{ __('Организация и должность') }}</dt>
                <dd>
                    @if($contact->organization)
                        {{ $contact->organization }}
                    @else
                        <span class="text-ink-muted">{{ __('Не указаны') }}</span>
                    @endif
                </dd>

                <dt class="text-ink-muted">{{ __('Получено') }}</dt>
                <dd>{{ $contact->created_at->setTimezone(\App\Models\Broadcast::TIMEZONE)->format('d.m.Y H:i') }}</dd>

                <dt class="text-ink-muted">{{ __('Язык страницы') }}</dt>
                <dd>{{ strtoupper($contact->locale) }}</dd>

                <dt class="text-ink-muted">{{ __('Письмо команде') }}</dt>
                <dd>
                    @if($contact->mail_sent_at)
                        <span class="badge badge--published">{{ __('Отправлено') }}</span>
                    @else
                        <span class="badge badge--rejected">{{ __('Не ушло') }}</span>
                        <p class="text-ink-muted text-sm mt-1">{{ __('Обращение сохранено здесь, но письмо на почту команды не отправилось. Проверьте настройки почты на сервере.') }}</p>
                        @if($contact->mail_error)
                            <p class="text-ink-muted text-sm mt-1 break-words">{{ $contact->mail_error }}</p>
                        @endif
                    @endif
                </dd>
            </dl>
        </div>

        <div class="card">
            <p class="text-ink-muted text-sm mb-2">{{ __('Сообщение') }}</p>
            <div class="text-reading whitespace-pre-line break-words">{{ $contact->message }}</div>
        </div>

        <x-admin.delete-button
            :action="route('admin.contact-messages.destroy', $contact)"
            :subject="$contact->name"
            :noun="__('обращение')" />
    </div>
</x-layouts.admin>
