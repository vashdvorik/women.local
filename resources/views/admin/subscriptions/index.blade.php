@php
    $tabs = [
        '' => [__('Все'), $counts['']],
        'private' => ['Private', $counts['private']],
        'active' => ['Active', $counts['active']],
        'community' => ['Community', $counts['community']],
        'none' => [__('Без тарифа'), $counts['none']],
        'expiring' => [__('Заканчиваются (30 дней)'), $counts['expiring']],
    ];
@endphp

<x-layouts.admin :title="__('Подписки')">
    <div class="space-y-4">
        <div class="card space-y-1 text-ui text-ink-muted">
            <p>
                {!! __('Тарифы зарегистрированных участниц. Бесплатного тарифа нет: <b class="text-ink">Community</b>, <b class="text-ink">Active</b> и <b class="text-ink">Private</b> платные, на год. Оплата проходит через банк сама; здесь тариф можно выдать вручную (перевод, подарок, исправление) или закрыть. Платежи банка смотрите в разделе «Платежи».') !!}
            </p>
        </div>

        <form method="POST" action="{{ route('admin.subscriptions.prices') }}" class="card space-y-4">
            @csrf
            @method('PUT')
            <x-forms.error-summary />

            <div>
                <h2 class="text-section font-semibold">{{ __('Цена подписки на год') }}</h2>
                <p class="field-hint mt-1">
                    {{ __('В рублях, целым числом. Новая цена сразу показывается участницам на странице «Подписка» и действует для новых платежей. Уже оплаченные подписки не меняются; счёт, который участница успела открыть в банке по старой цене, оплачивается по ней.') }}
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                @foreach(['community' => 'WOMEN’S HUB COMMUNITY', 'active' => 'WOMEN’S HUB ACTIVE', 'private' => 'WOMEN’S HUB PRIVATE'] as $key => $label)
                    <div class="space-y-1">
                        <label for="price-{{ $key }}" class="field-label">{{ $label }}</label>
                        <div class="flex items-center gap-2">
                            <input id="price-{{ $key }}" type="number" name="{{ $key }}" min="1" max="{{ $priceMax }}" step="1" required
                                   value="{{ old($key, $prices[$key]) }}" class="field-input !w-40" inputmode="numeric">
                            <span class="text-ui text-ink-muted">{{ __('руб. в год') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="submit" class="btn-primary">{{ __('Сохранить цены') }}</button>
        </form>

        <form method="POST" action="{{ route('admin.subscriptions.purchase') }}" class="card space-y-4">
            @csrf
            @method('PUT')

            <div>
                <h2 class="text-section font-semibold">{{ __('Покупка тарифов') }}</h2>
                <p class="field-hint mt-1">
                    {{ __('Отметьте тарифы, которые нельзя купить сразу: вместо «Купить» участница увидит «Оставить заявку». Вы позвоните ей и откроете покупку лично (раздел «Заявки на тариф» или карточка участницы). Так в начале работы платформы никто не купит большой тариф, пока в ней ещё никого нет.') }}
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
                @foreach(['community' => 'WOMEN’S HUB COMMUNITY', 'active' => 'WOMEN’S HUB ACTIVE', 'private' => 'WOMEN’S HUB PRIVATE'] as $key => $label)
                    <label class="flex items-center gap-2 rounded-lg border border-hairline px-3 py-2 text-ui">
                        <input type="checkbox" name="closed[]" value="{{ $key }}" class="rounded border-hairline" @checked(in_array($key, old('closed', $closed), true))>
                        <span><b class="text-ink">{{ $label }}</b><br><span class="text-ink-muted">{{ __('закрыть покупку, только по заявке') }}</span></span>
                    </label>
                @endforeach
            </div>

            <div class="space-y-1">
                <label for="manager-email" class="field-label">{{ __('Почта менеджера для заявок') }}</label>
                <input id="manager-email" type="email" name="manager_email" value="{{ old('manager_email', $managerEmail) }}" maxlength="191" class="field-input !max-w-sm" placeholder="manager@example.com">
                <p class="field-hint">{{ __('Сюда приходит письмо с именем, телефоном и тарифом, когда участница оставляет заявку. Если оставить пустым, заявки видны только в разделе «Заявки на тариф».') }}</p>
            </div>

            <button type="submit" class="btn-primary">{{ __('Сохранить') }}</button>
        </form>

        <div class="tabs overflow-x-auto no-scrollbar">
            @foreach($tabs as $key => [$label, $count])
                <a href="{{ route('admin.subscriptions.index', array_filter(['filter' => $key, 'q' => $search])) }}"
                   class="tab whitespace-nowrap @if($filter === (string) $key) tab--active @endif">
                    {{ $label }}<span class="text-ink-faint">{{ $count }}</span>
                </a>
            @endforeach
        </div>

        <form method="GET" class="flex w-full max-w-sm gap-2">
            @if($filter !== '') <input type="hidden" name="filter" value="{{ $filter }}"> @endif
            <input type="search" name="q" value="{{ $search }}" placeholder="{{ __('Поиск по имени, почте или телефону') }}" class="field-input">
            <button type="submit" class="btn-secondary">{{ __('Найти') }}</button>
        </form>

        @if($profiles->isEmpty())
            <div class="card text-center">
                <p class="text-reading text-ink-muted">{{ ($search !== '' || $filter !== '') ? __('Ничего не найдено.') : __('Участниц пока нет.') }}</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="min-w-[11rem]">{{ __('Участница') }}</th>
                            <th class="whitespace-nowrap">{{ __('Почта и телефон') }}</th>
                            <th class="whitespace-nowrap">{{ __('Тариф') }}</th>
                            <th class="whitespace-nowrap">{{ __('Действует до') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($profiles as $profile)
                            @php $plan = $profile->currentPlan(); @endphp
                            <tr>
                                <td>
                                    <a href="{{ route('admin.subscriptions.edit', $profile) }}" class="table-link">{{ $profile->full_name ?: __('Без имени') }}</a>
                                </td>
                                <td class="text-ink-muted">{{ $profile->email }}@if($profile->phone)<br>{{ $profile->phone }}@endif</td>
                                <td class="whitespace-nowrap">
                                    <span class="badge {{ $plan->isPaid() ? 'badge--published' : 'badge--draft' }}">{{ $plan->shortTitle() }}</span>
                                </td>
                                <td class="whitespace-nowrap text-ink-muted">
                                    @if($plan->isPaid())
                                        {{ $profile->plan_ends_at->format('d.m.Y') }}
                                        @if($profile->plan_ends_at->lte(now()->addDays(30))) <span class="badge badge--pending">{{ __('скоро') }}</span> @endif
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    <div class="flex items-center justify-end">
                                        <a href="{{ route('admin.subscriptions.edit', $profile) }}" class="btn-quiet">{{ __('Управлять') }}</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $profiles->links() }}
        @endif
    </div>
</x-layouts.admin>
