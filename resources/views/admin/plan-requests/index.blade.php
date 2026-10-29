@php
    $tabs = [
        '' => [__('В работе'), $counts['']],
        'new' => [__('Новые'), $counts['new']],
        'unlocked' => [__('Покупка открыта'), $counts['unlocked']],
        'purchased' => [__('Купили'), $counts['purchased']],
        'declined' => [__('Закрытые'), $counts['declined']],
        'all' => [__('Все'), $counts['all']],
    ];
    $statusLabels = [
        'new' => __('Ждёт звонка'),
        'contacted' => __('Позвонили'),
        'unlocked' => __('Покупка открыта'),
        'purchased' => __('Купила тариф'),
        'declined' => __('Закрыта'),
    ];
@endphp

<x-layouts.admin :title="__('Заявки на тариф')">
    <div class="space-y-4">
        <div class="card space-y-1 text-ui text-ink-muted">
            <p>{{ __('Сюда попадают заявки на тарифы, покупка которых закрыта («Подписки» → «Покупка тарифов»). Позвоните участнице по телефону из анкеты, договоритесь и нажмите «Открыть покупку»: у неё в кабинете на этом тарифе появится кнопка «Купить».') }}</p>
        </div>

        <div class="tabs overflow-x-auto no-scrollbar">
            @foreach($tabs as $key => [$label, $count])
                <a href="{{ route('admin.plan-requests.index', array_filter(['status' => $key])) }}"
                   class="tab whitespace-nowrap @if($status === (string) $key) tab--active @endif">
                    {{ $label }}<span class="text-ink-faint">{{ $count }}</span>
                </a>
            @endforeach
        </div>

        @if($requests->isEmpty())
            <div class="card text-center">
                <p class="text-reading text-ink-muted">{{ __('Заявок пока нет.') }}</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($requests as $item)
                    @php $person = $item->botUser; @endphp
                    <article class="card space-y-3">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="space-y-1">
                                <p class="text-section font-semibold">
                                    @if($person)
                                        <a href="{{ route('admin.subscriptions.edit', $person) }}" class="text-accent">{{ $person->full_name ?: $person->email }}</a>
                                    @else
                                        {{ __('Участница удалена') }}
                                    @endif
                                </p>
                                <p class="text-ui text-ink-muted">
                                    {{ __('Тариф') }}: <b class="text-ink">{{ $item->planEnum()->title() }}</b>
                                    · {{ $item->planEnum()->priceLabel() }} {{ __('в год') }}
                                    · {{ $item->created_at->format('d.m.Y H:i') }}
                                </p>
                                @if($person)
                                    <p class="text-ui">
                                        @if($person->phone)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $person->phone) }}" class="font-semibold text-accent">{{ $person->phone }}</a> · @endif
                                        <a href="mailto:{{ $person->email }}" class="text-accent">{{ $person->email }}</a>
                                        @if($person->telegram_username) · {{ '@'.$person->telegram_username }} @endif
                                    </p>
                                @endif
                            </div>
                            <span class="badge">{{ $statusLabels[$item->status] ?? $item->status }}</span>
                        </div>

                        @if($item->comment)
                            <p class="rounded-lg bg-surface-muted px-3 py-2 text-ui"><span class="text-ink-muted">{{ __('Комментарий участницы') }}:</span> {{ $item->comment }}</p>
                        @endif

                        <form method="POST" action="{{ route('admin.plan-requests.note', $item) }}" class="flex flex-wrap items-start gap-2">
                            @csrf
                            <input type="text" name="manager_note" value="{{ $item->manager_note }}" maxlength="1000" placeholder="{{ __('Заметка менеджера (о чём договорились)') }}" class="field-input flex-1 min-w-[16rem]">
                            <button type="submit" class="btn-secondary">{{ __('Сохранить заметку') }}</button>
                        </form>

                        @if(in_array($item->status, \App\Models\PlanRequest::OPEN_STATUSES, true))
                            <div class="flex flex-wrap items-center gap-2">
                                @if($item->status !== 'unlocked')
                                    <form method="POST" action="{{ route('admin.plan-requests.unlock', $item) }}" class="flex flex-wrap items-center gap-3">
                                        @csrf
                                        <button type="submit" class="btn-primary">{{ __('Открыть покупку') }}</button>
                                        <label class="flex items-center gap-2 text-ui text-ink-muted">
                                            <input type="checkbox" name="notify" value="1" class="rounded border-hairline" checked>
                                            {{ __('Сообщить участнице письмом') }}
                                        </label>
                                    </form>
                                @else
                                    <p class="text-ui text-ink-muted">{{ __('Покупка открыта :date. Заявка закроется, когда участница оплатит тариф.', ['date' => $item->unlocked_at?->format('d.m.Y H:i')]) }}</p>
                                @endif
                                <form method="POST" action="{{ route('admin.plan-requests.decline', $item) }}" onsubmit="return confirm('{{ __('Закрыть заявку без покупки?') }}')">
                                    @csrf
                                    <button type="submit" class="btn-secondary">{{ __('Закрыть заявку') }}</button>
                                </form>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>

            <div>{{ $requests->links() }}</div>
        @endif
    </div>
</x-layouts.admin>
