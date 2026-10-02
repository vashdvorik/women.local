@php
    $tabs = [
        '' => [__('Все'), $counts->sum()],
        \App\Models\BotUser::STATUS_PENDING => [__('Ожидают'), $counts[\App\Models\BotUser::STATUS_PENDING] ?? 0],
        \App\Models\BotUser::STATUS_APPROVED => [__('Одобрены'), $counts[\App\Models\BotUser::STATUS_APPROVED] ?? 0],
        \App\Models\BotUser::STATUS_REJECTED => [__('Отклонены'), $counts[\App\Models\BotUser::STATUS_REJECTED] ?? 0],
    ];
@endphp

<x-layouts.admin :title="__('Профили участниц')">
    <div class="space-y-4"
         x-data="{ ids: [], all: false, open: false,
                   pageIds: @js($profiles->pluck('id')->map(fn ($id) => (string) $id)->all()),
                   toggleAll() { this.ids = this.all ? [...this.pageIds] : []; },
                   sync() { this.all = this.ids.length === this.pageIds.length && this.pageIds.length > 0; } }">

        <div class="tabs">
            @foreach($tabs as $key => [$label, $count])
                <a href="{{ route('admin.profiles.index', array_filter(['status' => $key, 'q' => $search])) }}"
                   class="tab @if($status === (string) $key) tab--active @endif">
                    {{ $label }}<span class="text-ink-faint">{{ $count }}</span>
                </a>
            @endforeach
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <form method="GET" class="flex gap-2 w-full max-w-sm">
                @if($status !== '') <input type="hidden" name="status" value="{{ $status }}"> @endif
                <input type="search" name="q" value="{{ $search }}" placeholder="{{ __('Поиск по имени или @username') }}"
                       class="field-input">
                <button type="submit" class="btn-secondary">{{ __('Найти') }}</button>
            </form>

            <button type="button" class="btn-danger" x-show="ids.length > 0" x-cloak @click="open = true">
                {{ __('Удалить выбранные') }} (<span x-text="ids.length"></span>)
            </button>
        </div>

        @if($profiles->isEmpty())
            <div class="card text-center">
                <p class="text-reading text-ink-muted">
                    {{ ($search !== '' || $status !== '') ? __('Ничего не найдено.') : __('Профилей пока нет.') }}
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="w-10">
                                <input type="checkbox" x-model="all" @change="toggleAll()" aria-label="{{ __('Выбрать все') }}"
                                       class="rounded border-hairline text-accent focus:ring-accent-soft">
                            </th>
                            <th class="min-w-[11rem]"><x-admin.sort-link column="full_name" :label="__('Имя')" /></th>
                            <th class="whitespace-nowrap">Username</th>
                            <th class="whitespace-nowrap"><x-admin.sort-link column="status" :label="__('Статус')" /></th>
                            <th class="whitespace-nowrap"><x-admin.sort-link column="created_at" :label="__('Дата заявки')" /></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($profiles as $profile)
                            <tr>
                                <td>
                                    <input type="checkbox" name="ids[]" value="{{ $profile->id }}" form="bulk-form"
                                           x-model="ids" @change="sync()" aria-label="{{ __('Выбрать') }}"
                                           class="rounded border-hairline text-accent focus:ring-accent-soft">
                                </td>
                                <td>
                                    <a href="{{ route('admin.profiles.show', $profile) }}" class="table-link">
                                        {{ $profile->full_name ?: __('Без имени') }}
                                    </a>
                                </td>
                                <td class="whitespace-nowrap text-ink-muted">
                                    {{ $profile->telegram_username ? '@'.$profile->telegram_username : '—' }}
                                </td>
                                <td><x-admin.profile-status-badge :status="$profile->status" /></td>
                                <td class="whitespace-nowrap text-ink-muted">{{ $profile->created_at?->format('d.m.Y H:i') }}</td>
                                <td>
                                    <div class="flex items-center justify-end gap-1 whitespace-nowrap">
                                        @unless($profile->isApproved())
                                            <x-admin.confirm-button
                                                :action="route('admin.profiles.approve', $profile)"
                                                :label="__('Одобрить')"
                                                :title="__('Одобрить профиль?')"
                                                :message="__(':name получит уведомление в боте и доступ к личному кабинету.', ['name' => $profile->full_name ?: __('Участница')])" />
                                        @endunless
                                        @unless($profile->isRejected())
                                            <x-admin.confirm-button
                                                :action="route('admin.profiles.reject', $profile)"
                                                :label="__('Отклонить')"
                                                :title="__('Отклонить профиль?')"
                                                :message="$profile->isApproved()
                                                    ? __('Доступ к платформе будет закрыт, участница получит уведомление в боте.')
                                                    : __('Участница получит уведомление об отказе в боте.')"
                                                :danger="true" />
                                        @endunless
                                        <a href="{{ route('admin.profiles.edit', $profile) }}" class="btn-quiet">{{ __('Изменить') }}</a>
                                        <x-admin.delete-button
                                            :action="route('admin.profiles.destroy', $profile)"
                                            :subject="$profile->full_name ?: __('Без имени')"
                                            :noun="__('профиль')" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $profiles->links() }}
        @endif

        {{-- Массовое удаление: чекбоксы таблицы привязаны к форме атрибутом form. --}}
        <form id="bulk-form" method="POST" action="{{ route('admin.profiles.bulk-destroy') }}">
            @csrf
            @method('DELETE')
        </form>

        <template x-teleport="body">
            <div x-show="open" x-transition.opacity class="modal-backdrop" hidden
                 @keydown.escape.window="open = false" @click.self="open = false">
                <div class="modal" @click.stop>
                    <p class="modal__title">{{ __('Удалить выбранные профили?') }}</p>
                    <p class="text-reading text-ink-muted mt-2">
                        {{ __('Будет удалено профилей:') }} <b class="text-ink" x-text="ids.length"></b>.
                        {{ __('Вместе с ними удалятся их публикации в кабинете. Действие необратимо.') }}
                    </p>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="open = false" class="btn-quiet">{{ __('Отмена') }}</button>
                        <button type="submit" form="bulk-form" class="btn-primary">{{ __('Удалить') }}</button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</x-layouts.admin>
