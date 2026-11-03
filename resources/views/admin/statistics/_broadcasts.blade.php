{{-- Вкладка «Рассылки»: итоги и клики по кнопкам последних рассылок в Telegram. Данные: ActivityReport::broadcasts(). --}}
<div class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="card">
            <p class="field-hint uppercase">{{ __('Рассылок за 30 дней') }}</p>
            <p class="mt-2 text-[36px] font-semibold leading-none">{{ $count30 }}</p>
        </div>
        <div class="card">
            <p class="field-hint uppercase">{{ __('Доставлено сообщений') }}</p>
            <p class="mt-2 text-[36px] font-semibold leading-none">{{ $sent30 }}</p>
            <p class="mt-2 text-caption text-ink-muted">{{ __('за 30 дней, заблокировали бота: :n', ['n' => $blocked30]) }}</p>
        </div>
        <div class="card">
            <p class="field-hint uppercase">{{ __('Нажатий на кнопки') }}</p>
            <p class="mt-2 text-[36px] font-semibold leading-none text-accent">{{ $clicks30 }}</p>
            <p class="mt-2 text-caption text-ink-muted">{{ __('за 30 дней') }}</p>
        </div>
        <div class="card">
            <p class="field-hint uppercase">{{ __('Подключили Telegram') }}</p>
            <p class="mt-2 text-[36px] font-semibold leading-none">{{ $connected }}</p>
            <p class="mt-2 text-caption text-ink-muted">{{ __('из :paid участниц с действующим пакетом: остальные рассылку не получат', ['paid' => $paid]) }}</p>
        </div>
    </div>

    <div class="card text-ui text-ink-muted">
        <p>{{ __('«Прочитано» Telegram боту не сообщает, поэтому такой цифры нет. Показаны доставлено, блок и нажатия на кнопки: кнопки идут через личную ссылку получательницы, поэтому видно, кто и на какую нажал. Ссылки, вписанные прямо в текст сообщения, не считаются.') }}</p>
    </div>

    <div class="card">
        <h2 class="form-section-title">{{ __('Последние рассылки') }}</h2>

        @if($rows === [])
            <p class="text-ui text-ink-muted">{{ __('Отправленных рассылок пока нет.') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-ui">
                    <thead class="text-left text-caption uppercase text-ink-muted">
                        <tr>
                            <th class="py-2 pr-4">{{ __('Рассылка') }}</th>
                            <th class="py-2 pr-4">{{ __('Кому') }}</th>
                            <th class="py-2 pr-4 text-right">{{ __('Доставлено') }}</th>
                            <th class="py-2 pr-4 text-right">{{ __('Заблокировали') }}</th>
                            <th class="py-2 pr-4 text-right">{{ __('Ошибки') }}</th>
                            <th class="py-2 pr-4 text-right">{{ __('Нажали') }}</th>
                            <th class="py-2">{{ __('По кнопкам') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-hairline-soft align-top">
                        @foreach($rows as $row)
                            <tr>
                                <td class="py-3 pr-4">
                                    <a href="{{ route('admin.broadcasts.show', $row['id']) }}" class="table-link">{{ $row['title'] }}</a>
                                    <p class="text-caption text-ink-faint">{{ $row['date'] }}</p>
                                </td>
                                <td class="py-3 pr-4 text-ink-muted">{{ $row['audience'] }}</td>
                                <td class="py-3 pr-4 text-right">{{ $row['delivered'] }} <span class="text-ink-faint">/ {{ $row['total'] }}</span></td>
                                <td class="py-3 pr-4 text-right">{{ $row['blocked'] }}</td>
                                <td class="py-3 pr-4 text-right">{{ $row['failed'] }}</td>
                                <td class="py-3 pr-4 text-right">
                                    @if($row['trackable'])
                                        <span class="font-semibold">{{ $row['clickers'] }}</span> <span class="text-ink-faint">· {{ $row['ctr'] }}%</span>
                                    @else
                                        <span class="text-ink-faint">{{ __('без кнопок') }}</span>
                                    @endif
                                </td>
                                <td class="py-3 text-caption text-ink-muted">
                                    @forelse($row['buttons'] as $button)
                                        <div>{{ $button['label'] }}: {{ __(':clicks нажатий, :people чел.', ['clicks' => $button['clicks'], 'people' => $button['people']]) }}</div>
                                    @empty
                                        <span class="text-ink-faint">—</span>
                                    @endforelse
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-3 text-caption text-ink-muted">{{ __('«Нажали» — сколько разных получательниц нажали хотя бы на одну кнопку и какая это доля от доставленных.') }}</p>
        @endif
    </div>
</div>
