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
                    </div>

                    <dl class="grid gap-x-6 gap-y-1 text-ui sm:grid-cols-2">
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
                            <dd>{{ $profile->telegram_id }}</dd>
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

        <div class="flex flex-wrap items-center gap-2">
            @unless($profile->isApproved())
                <x-admin.confirm-button
                    :action="route('admin.profiles.approve', $profile)"
                    :label="__('Одобрить')"
                    trigger="btn-primary"
                    :title="__('Одобрить профиль?')"
                    :message="__('Участница получит уведомление в боте и доступ к личному кабинету.')" />
            @endunless
            @unless($profile->isRejected())
                <x-admin.confirm-button
                    :action="route('admin.profiles.reject', $profile)"
                    :label="__('Отклонить')"
                    trigger="btn-danger"
                    :title="__('Отклонить профиль?')"
                    :message="$profile->isApproved()
                        ? __('Доступ к платформе будет закрыт, участница получит уведомление в боте.')
                        : __('Участница получит уведомление об отказе в боте.')"
                    :danger="true" />
            @endunless
            <x-admin.delete-button
                :action="route('admin.profiles.destroy', $profile)"
                :subject="$profile->full_name ?: __('Без имени')"
                :noun="__('профиль')" />
        </div>
    </div>
</x-layouts.admin>
