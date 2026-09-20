<x-layouts.admin title="Инфопанель">
    @php
        $add = [
            ['route' => 'admin.posts.create', 'label' => 'публикацию'],
            ['route' => 'admin.opportunities.create', 'label' => 'возможность'],
            ['route' => 'admin.events.create', 'label' => 'новость'],
            ['route' => 'admin.experts.create', 'label' => 'эксперта'],
            ['route' => 'admin.projects.create', 'label' => 'проект'],
            ['route' => 'admin.albums.create', 'label' => 'фотоальбом'],
            ['route' => 'admin.videos.create', 'label' => 'видео'],
        ];
    @endphp

    <div class="form-column space-y-6">

        {{-- Напоминание о другом лагере: очередь модерации кабинетов --}}
        @if($pendingProfiles + $pendingPosts > 0)
            <a href="{{ route('admin.cabinets.dashboard') }}" class="card block hover:border-accent transition-colors">
                <p class="text-ui-strong font-semibold">В кабинетах участниц есть что решить</p>
                <p class="mt-1 text-ui text-ink-muted">
                    @if($pendingProfiles > 0)Профили: {{ $pendingProfiles }}.@endif
                    @if($pendingPosts > 0)Посты участниц: {{ $pendingPosts }}.@endif
                    Открыть «Кабинеты участниц» →
                </p>
            </a>
        @endif

        {{-- Быстрые действия --}}
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($add as $item)
                <a href="{{ route($item['route']) }}" class="btn-primary">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 3v10M3 8h10" stroke-linecap="round"/></svg>
                    Добавить {{ $item['label'] }}
                </a>
            @endforeach
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.subscribers.index') }}" class="btn-secondary">
                Подписчики{{ $subscriberCount > 0 ? " · {$subscriberCount}" : '' }}
            </a>
            <a href="{{ route('admin.tags.index') }}" class="btn-secondary">Теги</a>
            <a href="{{ route('admin.settings.edit') }}" class="btn-secondary">Настройки сайта</a>
        </div>

        <p class="text-caption text-ink-muted">
            {{ trans_choice('dashboard.drafts', $draftCount, ['count' => $draftCount]) }}
        </p>

        {{-- Инструкция --}}
        <div class="card">
            <h2 class="text-ui-strong font-semibold mb-4">Как пользоваться админкой</h2>
            <ol class="space-y-3 text-reading text-ink-muted list-decimal pl-5 marker:text-ink-faint marker:font-semibold">
                <li>
                    Админка разделена на два лагеря, переключатель — вверху меню.
                    <b class="text-ink">«Внешний сайт»</b> — всё, что видят посетители: новости, публикации, эксперты и так далее (этот раздел).
                    <b class="text-ink">«Кабинеты участниц»</b> — профили и посты участниц, статистика и настройки кабинета.
                </li>
                <li>
                    Разделы сайта — в меню слева. У публикаций, возможностей, проектов и медиа два пункта:
                    <b class="text-ink">«Все …»</b> — список уже добавленного, <b class="text-ink">«Добавить …»</b> — форма новой записи.
                </li>
                <li>
                    Чтобы изменить запись, откройте список и нажмите <b class="text-ink">«Изменить»</b> в её строке.
                    Кнопка <b class="text-ink">«Удалить»</b> спросит подтверждение.
                </li>
                <li>
                    Статус записи: <b class="text-ink">черновик</b> виден только здесь, в админке;
                    <b class="text-ink">опубликовано</b> — показывается на сайте. Переключается галочкой в форме.
                </li>
                <li>
                    Русский текст обязателен. Румынский и английский — по желанию: там, где перевод не заполнен,
                    сайт покажет русский вариант.
                </li>
                <li>
                    Картинки загружаются прямо в форме — перетащите файл или выберите с компьютера.
                    Кадрирование под нужный размер происходит автоматически.
                </li>
                <li>
                    Порядок проектов, экспертов, новостей и видео на сайте задаётся стрелками <b class="text-ink">↑ ↓</b> в списке.
                </li>
                <li>
                    Кто подписался на новости в подвале сайта — раздел <b class="text-ink">«Подписчики»</b>. Список выгружается
                    кнопками <b class="text-ink">«Скачать CSV»</b> и <b class="text-ink">«Список почт (.txt)»</b> —
                    их можно загрузить в программу рассылки.
                </li>
            </ol>
        </div>

    </div>
</x-layouts.admin>
