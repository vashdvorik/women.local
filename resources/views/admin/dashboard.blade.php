<x-layouts.admin :title="__('Инфопанель')">
    @php
        $add = [
            ['route' => 'admin.events.create', 'label' => __('новость')],
            ['route' => 'admin.opportunities.create', 'label' => __('возможность')],
            ['route' => 'admin.learning-videos.create', 'label' => __('обучающее видео')],
            ['route' => 'admin.projects.create', 'label' => __('проект')],
            ['route' => 'admin.experts.create', 'label' => __('эксперта')],
            ['route' => 'admin.albums.create', 'label' => __('фотоальбом')],
            ['route' => 'admin.videos.create', 'label' => __('видео')],
        ];
        $waiting = collect($queues)->sum('count');
    @endphp

    <div class="form-column space-y-6">

        {{-- Что требует внимания: очереди, в которых что-то ждёт решения администратора --}}
        <section aria-labelledby="attention-title" class="space-y-3">
            <h2 id="attention-title" class="text-ui-strong font-semibold">
                {{ $waiting > 0 ? __('Требует внимания') : __('Всё разобрано') }}
            </h2>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($queues as $queue)
                    <a href="{{ $queue['url'] }}" class="card block hover:border-accent transition-colors {{ $queue['count'] === 0 ? 'opacity-70' : '' }}">
                        <p class="field-hint uppercase">{{ $queue['label'] }}</p>
                        <p class="mt-2 text-[36px] font-semibold leading-none">{{ $queue['count'] }}</p>
                        <p class="mt-2 text-caption text-ink-muted">{{ $queue['count'] > 0 ? $queue['open'] : __('Очередь пуста') }}</p>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- Быстрые действия --}}
        <section aria-labelledby="add-title" class="space-y-3">
            <h2 id="add-title" class="text-ui-strong font-semibold">{{ __('Добавить') }}</h2>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($add as $item)
                    <a href="{{ route($item['route']) }}" class="btn-primary">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 3v10M3 8h10" stroke-linecap="round"/></svg>
                        {{ __('Добавить :item', ['item' => $item['label']]) }}
                    </a>
                @endforeach
            </div>
        </section>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.profiles.index') }}" class="btn-secondary">
                {{ __('Профили участниц · :count видимы другим', ['count' => $approvedProfiles]) }}
            </a>
            <a href="{{ route('admin.broadcasts.index') }}" class="btn-secondary">{{ __('Рассылка в Telegram') }}</a>
            <a href="{{ route('admin.statistics.index') }}" class="btn-secondary">{{ __('Статистика') }}</a>
            <a href="{{ route('admin.subscribers.index') }}" class="btn-secondary">
                {{ __('Подписчики') }}{{ $subscriberCount > 0 ? " · {$subscriberCount}" : '' }}
            </a>
        </div>

        <p class="text-caption text-ink-muted">
            {{ trans_choice('dashboard.drafts', $draftCount, ['count' => $draftCount]) }}
        </p>

        {{-- Инструкция --}}
        <div class="card">
            <h2 class="text-ui-strong font-semibold mb-4">{{ __('Как пользоваться админкой') }}</h2>
            <ol class="space-y-3 text-reading text-ink-muted list-decimal pl-5 marker:text-ink-faint marker:font-semibold">
                <li>
                    {{ __('Меню слева собрано по задачам:') }}
                    {!! __('<b class="text-ink">«Участницы»</b> — профили, инициативы, вопросы AI-менеджеру, статистика; <b class="text-ink">«Пакеты и оплата»</b> — цены, заявки, платежи; <b class="text-ink">«Контент»</b> — новости, возможности и обучение, проекты, эксперты, медиатека; <b class="text-ink">«Общение»</b> — рассылка в Telegram, обращения, подписчики; <b class="text-ink">«Настройки»</b> — сайта и кабинетов.') !!}
                </li>
                <li>{!! __('Числа в кружках — сколько записей ждёт вашего решения. Те же очереди собраны на этой странице: нажмите карточку, чтобы открыть нужный список.') !!}</li>
                <li>{!! __('У каждого раздела один пункт меню. Новая запись — кнопка <b class="text-ink">«Добавить»</b> вверху списка; чтобы изменить запись, нажмите <b class="text-ink">«Изменить»</b> в её строке. Кнопка <b class="text-ink">«Удалить»</b> спросит подтверждение.') !!}</li>
                <li>{!! __('Новая участница регистрируется на сайте, заполняет анкету и выбирает пакет. Анкета попадает в <b class="text-ink">«Профили участниц»</b>. После первой оплаты (или подарка пакета) профиль сам становится видимым другим участницам. Видимость, <b class="text-ink">«Режим невидимки»</b> и блокировку вы меняете в списке профилей: у каждой кнопки есть подсказка.') !!}</li>
                <li>{!! __('Инициативы, которые участницы публикуют в кабинете, сначала попадают в <b class="text-ink">«Инициативы участниц»</b>. Пока инициатива не одобрена, её видит только автор.') !!}</li>
                <li>{!! __('<b class="text-ink">«Возможности/обучение»</b> — карточки для сайта и кабинета. Галочками в форме выбирается, где карточка показывается: на открытом сайте, в «Возможностях» кабинета, в «Обучении». <b class="text-ink">«Обучающие видео»</b> — отдельные видео для раздела «Обучение» в кабинете.') !!}</li>
                <li>{!! __('<b class="text-ink">«Медиатека»</b> объединяет фотоальбомы, видео и каталоги, которые выпустила организация.') !!}</li>
                <li>{!! __('Статус записи: <b class="text-ink">черновик</b> виден только здесь, в админке; <b class="text-ink">опубликовано</b> — показывается на сайте. Переключается галочкой в форме.') !!}</li>
                <li>{{ __('Русский текст обязателен. Румынский и английский — по желанию: там, где перевод не заполнен, сайт покажет русский вариант.') }}</li>
                <li>{{ __('Картинки загружаются прямо в форме — перетащите файл или выберите с компьютера. Кадрирование под нужный размер происходит автоматически.') }}</li>
                <li>{!! __('Порядок проектов, экспертов, новостей и видео на сайте задаётся стрелками <b class="text-ink">↑ ↓</b> в списке.') !!}</li>
                <li>{!! __('Кто подписался на новости в подвале сайта — раздел <b class="text-ink">«Подписчики»</b>. Список выгружается кнопками <b class="text-ink">«Скачать CSV»</b> и <b class="text-ink">«Список почт (.txt)»</b> — их можно загрузить в программу рассылки.') !!}</li>
            </ol>
        </div>

    </div>
</x-layouts.admin>
