@php
    $tabs = [
        '' => [__('Все'), $counts['']],
        'community' => ['Community', $counts['community']],
        'private' => ['Private', $counts['private']],
        'open' => ['Open', $counts['open']],
        'expiring' => [__('Заканчиваются (30 дней)'), $counts['expiring']],
    ];
@endphp

<x-layouts.admin :title="__('Подписки')">
    <div class="space-y-4">
        <div class="card space-y-1 text-ui text-ink-muted">
            <p>
                {!! __('Тарифы одобренных участниц: <b class="text-ink">Open</b> бесплатный, <b class="text-ink">Community</b> и <b class="text-ink">Private</b> платные, на год. Оплата проходит через банк сама; здесь тариф можно выдать вручную (перевод, подарок, исправление) или закрыть. Платежи банка смотрите в разделе «Платежи».') !!}
            </p>
        </div>

        <form method="POST" action="{{ route('admin.subscriptions.prices') }}" class="card space-y-4">
            @csrf
            @method('PUT')
            <x-forms.error-summary />

            <div>
                <h2 class="text-section font-semibold">{{ __('Цена подписки на год') }}</h2>
                <p class="field-hint mt-1">
                    {{ __('В рублях ПМР, целым числом. Новая цена сразу показывается участницам на странице «Подписка» и действует для новых платежей. Уже оплаченные подписки не меняются; счёт, который участница успела открыть в банке по старой цене, оплачивается по ней.') }}
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                @foreach(['community' => 'WOMEN’S HUB COMMUNITY', 'private' => 'WOMEN’S HUB PRIVATE'] as $key => $label)
                    <div class="space-y-1">
                        <label for="price-{{ $key }}" class="field-label">{{ $label }}</label>
                        <div class="flex items-center gap-2">
                            <input id="price-{{ $key }}" type="number" name="{{ $key }}" min="1" max="{{ $priceMax }}" step="1" required
                                   value="{{ old($key, $prices[$key]) }}" class="field-input !w-40" inputmode="numeric">
                            <span class="text-ui text-ink-muted">{{ __('руб. ПМР в год') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="submit" class="btn-primary">{{ __('Сохранить цены') }}</button>
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
            <input type="search" name="q" value="{{ $search }}" placeholder="{{ __('Поиск по имени или @username') }}" class="field-input">
            <button type="submit" class="btn-secondary">{{ __('Найти') }}</button>
        </form>

        @if($profiles->isEmpty())
            <div class="card text-center">
                <p class="text-reading text-ink-muted">{{ ($search !== '' || $filter !== '') ? __('Ничего не найдено.') : __('Одобренных участниц пока нет.') }}</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="min-w-[11rem]">{{ __('Участница') }}</th>
                            <th class="whitespace-nowrap">Username</th>
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
                                <td class="whitespace-nowrap text-ink-muted">{{ $profile->telegram_username ? '@'.$profile->telegram_username : '—' }}</td>
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
