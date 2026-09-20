<x-layouts.admin title="Инфопанель">
    <div class="form-column space-y-6">

        {{-- Очередь модерации --}}
        <div class="grid gap-3 sm:grid-cols-2">
            <a href="{{ route('admin.profiles.index', ['status' => 'pending']) }}" class="card block hover:border-accent transition-colors">
                <p class="field-hint uppercase">Профили ждут решения</p>
                <p class="mt-2 text-[36px] font-semibold leading-none">{{ $pendingProfiles }}</p>
                <p class="mt-2 text-caption text-ink-muted">
                    {{ $pendingProfiles > 0 ? 'Открыть очередь профилей' : 'Очередь пуста' }}
                </p>
            </a>
            <a href="{{ route('admin.member-posts.index', ['status' => 'pending']) }}" class="card block hover:border-accent transition-colors">
                <p class="field-hint uppercase">Посты участниц ждут решения</p>
                <p class="mt-2 text-[36px] font-semibold leading-none">{{ $pendingPosts }}</p>
                <p class="mt-2 text-caption text-ink-muted">
                    {{ $pendingPosts > 0 ? 'Открыть очередь постов' : 'Очередь пуста' }}
                </p>
            </a>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.profiles.index') }}" class="btn-secondary">
                Профили участниц · {{ $approvedProfiles }} одобрено
            </a>
            <a href="{{ route('admin.member-posts.index') }}" class="btn-secondary">Посты участниц</a>
            <a href="{{ route('admin.statistics.index') }}" class="btn-secondary">Статистика</a>
            <a href="{{ route('admin.cabinets.settings') }}" class="btn-secondary">Настройки кабинетов</a>
        </div>

        {{-- Памятка --}}
        <div class="card">
            <h2 class="text-ui-strong font-semibold mb-4">Как устроен этот раздел</h2>
            <ol class="space-y-3 text-reading text-ink-muted list-decimal pl-5 marker:text-ink-faint marker:font-semibold">
                <li>
                    Здесь всё, что относится к <b class="text-ink">закрытому кабинету участниц</b> и Telegram-боту.
                    Публичный сайт (новости, публикации, эксперты и т. д.) — на вкладке <b class="text-ink">«Внешний сайт»</b> вверху меню.
                </li>
                <li>
                    Новая участница регистрируется через бота и попадает в <b class="text-ink">«Профили участниц»</b>.
                    Кнопки <b class="text-ink">«Одобрить»</b> и <b class="text-ink">«Отклонить»</b> сразу отправляют ей уведомление в Telegram.
                    Доступ в кабинет получает только одобренная.
                </li>
                <li>
                    Посты, которые участницы публикуют в кабинете, сначала попадают в <b class="text-ink">«Посты участниц»</b>.
                    Пока пост не одобрен, его видит только автор; после одобрения он появляется у всех и уходит участницам уведомлением.
                </li>
                <li>
                    <b class="text-ink">«Статистика»</b> — срез по заявкам, заполненности профилей и активности кабинета, с выгрузкой в PDF.
                </li>
                <li>
                    <b class="text-ink">«Настройки кабинетов»</b>: тема оформления кабинета, ИИ-провайдеры для поиска и AI-помощника,
                    а также база знаний, которую помощник использует в ответах.
                </li>
            </ol>
        </div>
    </div>
</x-layouts.admin>
