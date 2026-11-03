<x-layouts.admin :title="$profile->full_name ?: __('Профиль участницы')">
    <x-slot:actions>
        <a href="{{ route('admin.profiles.index') }}" class="btn-secondary">{{ __('К списку') }}</a>
        <a href="{{ route('admin.profiles.edit', $profile) }}" class="btn-secondary">{{ __('Изменить') }}</a>
    </x-slot:actions>

    <div class="form-column space-y-6">
        <div class="card">
            <div class="flex items-start gap-4">
                @if($profile->avatar_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($profile->avatar_path) }}" alt=""
                         class="h-20 w-20 shrink-0 rounded-md object-cover">
                @else
                    <span class="grid h-20 w-20 shrink-0 place-items-center rounded-md bg-surface-sunken text-section text-ink-faint">
                        {{ mb_strtoupper(mb_substr((string) $profile->full_name, 0, 1)) ?: '—' }}
                    </span>
                @endif

                <div class="min-w-0 flex-1 space-y-2">
                    <div class="flex flex-wrap items-center gap-3">
                        <h2 class="text-section font-semibold">{{ $profile->full_name ?: __('Без имени') }}</h2>
                        <x-admin.profile-status-badge :status="$profile->status" />
                        @if($profile->is_hidden)
                            <span class="badge badge--draft" title="{{ __('Режим невидимки: другие участницы этот профиль не видят и не находят.') }}">{{ __('Невидимка') }}</span>
                        @endif
                    </div>

                    <dl class="grid gap-x-6 gap-y-1 text-ui sm:grid-cols-2">
                        <div class="flex gap-2">
                            <dt class="text-ink-muted">{{ __('Почта:') }}</dt>
                            <dd>
                                @if($profile->email)
                                    <a href="mailto:{{ $profile->email }}" class="text-accent">{{ $profile->email }}</a>
                                    @unless($profile->hasVerifiedEmail()) <span class="badge badge--draft">{{ __('не подтверждена') }}</span> @endunless
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                        @if($profile->contact_email)
                            <div class="flex gap-2">
                                <dt class="text-ink-muted">{{ __('Почта для связи:') }}</dt>
                                <dd><a href="mailto:{{ $profile->contact_email }}" class="text-accent">{{ $profile->contact_email }}</a></dd>
                            </div>
                        @endif
                        <div class="flex gap-2">
                            <dt class="text-ink-muted">{{ __('Телефон:') }}</dt>
                            <dd>
                                @if($profile->phone)
                                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $profile->phone) }}" class="text-accent">{{ $profile->phone }}</a>
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                        <div class="flex gap-2">
                            <dt class="text-ink-muted">Telegram:</dt>
                            <dd>
                                @if($profile->telegram_username)
                                    <a href="https://t.me/{{ $profile->telegram_username }}" target="_blank" rel="noopener"
                                       class="text-accent">{{ '@'.$profile->telegram_username }}</a>
                                @else
                                    —
                                @endif
                            </dd>
                        </div>
                        <div class="flex gap-2">
                            <dt class="text-ink-muted">Telegram ID:</dt>
                            <dd>{{ $profile->telegram_id ?? '—' }}</dd>
                        </div>
                        <div class="flex gap-2">
                            <dt class="text-ink-muted">{{ __('Заявка:') }}</dt>
                            <dd>{{ $profile->created_at?->format('d.m.Y H:i') }}</dd>
                        </div>
                        <div class="flex gap-2">
                            <dt class="text-ink-muted">{{ __('Одобрена:') }}</dt>
                            <dd>{{ $profile->approved_at?->format('d.m.Y H:i') ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        {{-- Активность в кабинете: когда была, как часто заходит, чем пользуется (учёт идёт с запуска, см. «Статистика → Активность») --}}
        <div class="card space-y-3">
            <h2 class="form-section-title">{{ __('Активность в кабинете') }}</h2>
            @if($activity['daysTotal'] === 0)
                <p class="text-ui text-ink-muted">{{ __('Заходов в кабинет после включения учёта пока нет.') }}@if($profile->last_seen_at) {{ __('Последний вход: :date.', ['date' => $activity['lastSeen']]) }}@endif</p>
            @else
                <dl class="grid gap-x-8 gap-y-2 text-ui sm:grid-cols-2">
                    <div class="flex justify-between gap-3"><dt class="text-ink-muted">{{ __('Последний вход') }}</dt><dd class="font-semibold">{{ $activity['lastSeen'] ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-ink-muted">{{ __('Дней с заходом за 30 дней') }}</dt><dd class="font-semibold">{{ $activity['days30'] }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-ink-muted">{{ __('Дней с заходом всего') }}</dt><dd class="font-semibold">{{ $activity['daysTotal'] }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-ink-muted">{{ __('Первый учтённый заход') }}</dt><dd class="font-semibold">{{ $activity['firstSeen'] ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-ink-muted">{{ __('Нажатий на кнопки рассылок') }}</dt><dd class="font-semibold">{{ $activity['clicks'] }}</dd></div>
                </dl>
                <ul class="divide-y divide-hairline-soft text-ui">
                    @foreach($activity['usage'] as $row)
                        <li class="flex justify-between gap-3 py-1.5"><span>{{ $row['label'] }}</span><span class="text-ink-muted">{{ __(':hits раз, :days дн.', ['hits' => $row['hits'], 'days' => $row['days']]) }}</span></li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="card space-y-5">
            <div>
                <h3 class="form-section-title">{{ __('Что представляет') }}</h3>
                <p class="text-reading whitespace-pre-line">{{ $profile->description ?: '—' }}</p>
            </div>
            <div>
                <h3 class="form-section-title">{{ __('Что ищет и чем может быть полезна') }}</h3>
                <p class="text-reading whitespace-pre-line">{{ $profile->expectation ?: '—' }}</p>
            </div>
        </div>

        {{-- Состояние профиля и кнопки модерации: что значит каждое состояние и что произойдёт при нажатии, написано рядом. --}}
        <div class="card space-y-4">
            <h3 class="form-section-title">{{ __('Видимость и доступ') }}</h3>
            <dl class="space-y-2 text-reading">
                <div>
                    <dt class="inline font-semibold">{{ $profile->isRejected() ? __('Заблокирована.') : __('Доступ открыт.') }}</dt>
                    <dd class="inline text-ink-muted">{{ $profile->isRejected()
                        ? __('Войти в кабинет она не может, рассылки не приходят. Нажмите «Разблокировать», чтобы вернуть доступ.')
                        : __('Она пользуется кабинетом. Заблокировать можно в любой момент: вход закроется, а ей придёт уведомление.') }}</dd>
                </div>
                @unless($profile->isRejected())
                    <div>
                        <dt class="inline font-semibold">{{ $profile->isApproved() ? __('Видима другим.') : __('Скрыта от других.') }}</dt>
                        <dd class="inline text-ink-muted">{{ $profile->isApproved()
                            ? __('Её находят в каталоге, поиске, подборе контактов и AI-помощнике, пока у неё действует платный пакет.')
                            : __('Её нет в каталоге, поиске, подборе контактов и AI-помощнике. Кабинет, пакет и рассылки работают.') }}</dd>
                    </div>
                    <div>
                        <dt class="inline font-semibold">{{ $profile->is_hidden ? __('Режим невидимки включён.') : __('Режим невидимки выключен.') }}</dt>
                        <dd class="inline text-ink-muted">{{ $profile->is_hidden
                            ? __('Другие участницы её не видят и не находят, а публиковать инициативы она не может. Рассылки она получает.')
                            : __('Включайте для администратора, спонсора или партнёра: человек остаётся внутри платформы, но другие его не замечают.') }}</dd>
                    </div>
                @endunless
            </dl>
            <div class="flex flex-wrap items-center gap-2">
                <x-admin.profile-moderation :profile="$profile" :page="true" />
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <x-admin.delete-button
                :action="route('admin.profiles.destroy', $profile)"
                :subject="$profile->full_name ?: __('Без имени')"
                :noun="__('профиль')" />
        </div>
    </div>
</x-layouts.admin>
