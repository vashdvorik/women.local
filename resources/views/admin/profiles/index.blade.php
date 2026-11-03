@php
    $tabs = [
        '' => [__('Все'), $counts->sum()],
        \App\Models\BotUser::STATUS_PENDING => [__('Скрыты от других'), $counts[\App\Models\BotUser::STATUS_PENDING] ?? 0],
        \App\Models\BotUser::STATUS_APPROVED => [__('Видимы другим'), $counts[\App\Models\BotUser::STATUS_APPROVED] ?? 0],
        \App\Models\BotUser::STATUS_REJECTED => [__('Заблокированы'), $counts[\App\Models\BotUser::STATUS_REJECTED] ?? 0],
    ];
@endphp

<x-layouts.admin :title="__('Профили участниц')">
    <div class="space-y-4"
         x-data="{ ids: [], all: false, open: false,
                   pageIds: @js($profiles->pluck('id')->map(fn ($id) => (string) $id)->all()),
                   toggleAll() { this.ids = this.all ? [...this.pageIds] : []; },
                   sync() { this.all = this.ids.length === this.pageIds.length && this.pageIds.length > 0; } }">

        {{-- Памятка: три независимые вещи, которые часто путают --}}
        <details class="card text-reading">
            <summary class="cursor-pointer font-semibold">{{ __('Что значат кнопки и состояния') }}</summary>
            <ul class="mt-3 space-y-2 text-ink-muted">
                <li><span class="font-semibold text-ink">{{ __('Видима другим / Скрыта от других.') }}</span> {{ __('Показывать ли профиль другим участницам в каталоге, поиске, подборе контактов и AI-помощнике. Работает, только пока у неё действует платный пакет. Участнице ничего не отправляется, на её кабинет и рассылки не влияет. После регистрации профиль скрыт; после первой оплаты или подарка пакета он становится видимым сам, дальше решает администратор.') }}</li>
                <li><span class="font-semibold text-ink">{{ __('Режим невидимки.') }}</span> {{ __('Для администраторов, спонсоров и партнёров: человек пользуется платформой, получает рассылки, но другие его не видят и не находят, а инициативы он публиковать не может. Сильнее, чем «Скрыта от других»: перекрывает «Видима другим».') }}</li>
                <li><span class="font-semibold text-ink">{{ __('Заблокировать / Разблокировать.') }}</span> {{ __('Единственное, что закрывает вход в кабинет. Блокировать можно любую участницу в любое время. Ей придёт уведомление, рассылки прекратятся, профиль пропадёт из каталога; данные и платежи сохраняются. Разблокировка возвращает всё как было, уведомления нет.') }}</li>
                <li><span class="font-semibold text-ink">{{ __('Удалить.') }}</span> {{ __('Профиль стирается из базы безвозвратно.') }}</li>
            </ul>
        </details>

        <div class="tabs">
            @foreach($tabs as $key => [$label, $count])
                <a href="{{ route('admin.profiles.index', array_filter(['status' => $key, 'q' => $search])) }}"
                   class="tab @if($status === (string) $key && ! $onlyHidden) tab--active @endif">
                    {{ $label }}<span class="text-ink-faint">{{ $count }}</span>
                </a>
            @endforeach
            {{-- Режим невидимки: администратор, спонсоры, партнёры. --}}
            <a href="{{ route('admin.profiles.index', array_filter(['hidden' => 1, 'q' => $search])) }}"
               class="tab @if($onlyHidden) tab--active @endif">
                {{ __('Режим невидимки') }}<span class="text-ink-faint">{{ $hiddenCount }}</span>
            </a>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <form method="GET" class="flex gap-2 w-full max-w-sm">
                @if($status !== '') <input type="hidden" name="status" value="{{ $status }}"> @endif
                @if($onlyHidden) <input type="hidden" name="hidden" value="1"> @endif
                <input type="search" name="q" value="{{ $search }}" placeholder="{{ __('Поиск по имени или @username') }}"
                       class="field-input">
                <button type="submit" class="btn-secondary">{{ __('Найти') }}</button>
            </form>

            <div class="flex flex-wrap items-center gap-2">
                {{-- Файл с теми же участницами, что на экране (вкладка и поиск учитываются) --}}
                <a href="{{ route('admin.profiles.export', array_filter(['status' => $status, 'q' => $search, 'hidden' => $onlyHidden ? 1 : null])) }}" class="btn-secondary">
                    {{ __('Выгрузить в файл') }}
                </a>
                <button type="button" class="btn-danger" x-show="ids.length > 0" x-cloak @click="open = true">
                    {{ __('Удалить выбранные') }} (<span x-text="ids.length"></span>)
                </button>
            </div>
        </div>

        @if($profiles->isEmpty())
            <div class="card text-center">
                <p class="text-reading text-ink-muted">
                    {{ ($search !== '' || $status !== '' || $onlyHidden) ? __('Ничего не найдено.') : __('Профилей пока нет.') }}
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="data-table [&_td]:!px-2 [&_th]:!px-2">
                    <thead>
                        <tr>
                            <th class="w-10">
                                <input type="checkbox" x-model="all" @change="toggleAll()" aria-label="{{ __('Выбрать все') }}"
                                       class="rounded border-hairline text-accent focus:ring-accent-soft">
                            </th>
                            <th class="min-w-[11rem]"><x-admin.sort-link column="full_name" :label="__('Имя')" /></th>
                            <th class="whitespace-nowrap">{{ __('Почта и телефон') }}</th>
                            <th class="whitespace-nowrap" title="{{ __('Нажмите на переключатель: перед изменением откроется окно с пояснением.') }}"><x-admin.sort-link column="status" :label="__('Видимость')" /></th>
                            <th class="hidden whitespace-nowrap xl:table-cell"><x-admin.sort-link column="created_at" :label="__('Дата заявки')" /></th>
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
                                <td class="text-ink-muted">
                                    {{ $profile->email }}@if($profile->phone)<br>{{ $profile->phone }}@endif
                                </td>
                                <td>
                                    <div class="flex flex-col items-start gap-1.5 py-1.5">
                                        <x-admin.profile-moderation :profile="$profile" part="switches" />
                                    </div>
                                </td>
                                <td class="hidden whitespace-nowrap text-ink-muted xl:table-cell" title="{{ $profile->created_at?->format('d.m.Y H:i') }}">{{ $profile->created_at?->format('d.m.Y') }}</td>
                                <td>
                                    <div class="flex items-center justify-end gap-2 whitespace-nowrap">
                                        <a href="{{ route('admin.profiles.edit', $profile) }}" class="btn-quiet">{{ __('Изменить') }}</a>
                                        <x-admin.profile-moderation :profile="$profile" part="lock" />
                                        <x-admin.delete-button
                                            :action="route('admin.profiles.destroy', $profile)"
                                            :subject="$profile->full_name ?: __('Без имени')"
                                            :noun="__('профиль')"
                                            :compact="true" />
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
