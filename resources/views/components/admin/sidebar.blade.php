@php
    // «Публикации» (статьи) пока не используются на сайте: пункт меню скрыт, страницы /admin/news открываются по прямой
    // ссылке. Чтобы вернуть пункт в «Медиатеку», поставьте true.
    $showPublications = false;

    // Группы меню сворачиваются; группа текущей страницы раскрыта всегда. «Медиатека» вложена в «Контент»: страницы
    // фотоальбомов, видео и каталогов раскрывают обе.
    $activeGroups = array_values(array_filter([
        \App\Support\AdminSection::key(),
        request()->routeIs('admin.albums.*', 'admin.videos.*', 'admin.posts.*', 'admin.catalogs.*') ? 'media' : null,
    ]));

    // Что ждёт решения администратора: числа стоят рядом с пунктами, а их сумма — на «Инфопанели».
    $pendingProfiles = \App\Models\BotUser::pending()->count();
    $pendingMemberPosts = \App\Models\Opportunity::pending()->count();

    // Новые заявки на закрытые пакеты: менеджеру нужно позвонить.
    $newPlanRequests = \App\Models\PlanRequest::where('status', \App\Models\PlanRequest::STATUS_NEW)->count();

    // Вопросы участниц AI-менеджеру, на которые он не нашёл ответа: ждут, пока админ дополнит знания или ответит сам.
    $managerQuestions = \App\Models\ManagerMessage::unanswered()->count();

    // Платежи, которые банк подтвердил, а мы нет (расхождение суммы и т. п.): ждут решения администратора.
    $problemPayments = \App\Models\Payment::where('status', \App\Models\Payment::STATUS_VERIFYING)->where('updated_at', '<=', now()->subMinutes(15))->count();

    // Непрочитанные обращения из формы «Связаться с нами» на странице «Контакты».
    $unreadContacts = \App\Models\ContactMessage::unread()->count();

    $attention = $pendingProfiles + $pendingMemberPosts + $newPlanRequests + $managerQuestions + $problemPayments + $unreadContacts;
@endphp

<aside {{ $attributes->merge(['class' => 'admin-sidebar']) }}>
    {{-- Надпись «Women's Hub» (logo-wordmark.webp): для рабочего инструмента монограмма не нужна, а название читается
         крупнее, чем в полном логотипе при той же высоте. Цветной вариант: меню светлое. --}}
    <a href="{{ route('admin.dashboard') }}"
       class="admin-brand-logo h-header flex items-center px-4 border-b border-hairline shrink-0"
       title="{{ config('app.name') }} — {{ __('Админпанель') }}">
        <img src="{{ asset_v('themes/public/miro/images/brand/logo-wordmark.webp') }}" alt="{{ config('app.name') }} — {{ __('Админпанель') }}"
             class="h-10 w-auto max-w-full object-contain object-left">
    </a>

    {{-- Одно меню для всей админки, сгруппированное по задачам администратора: участницы, пакеты и оплата, контент,
         общение, настройки. Заголовки групп — кнопки сворачивания. --}}
    <nav class="py-2 flex-1" style="visibility:hidden" data-active="{{ json_encode($activeGroups) }}" x-data="sidebarNav(@js($activeGroups))">
        <a href="{{ route('admin.dashboard') }}"
           class="nav-link @if(request()->routeIs('admin.dashboard')) nav-link--active @endif">
            <x-admin.nav-icon name="dashboard" /><span>{{ __('Инфопанель') }}</span>
            @if($attention > 0)<span class="nav-counter" title="{{ __('Требует внимания') }}">{{ $attention }}</span>@endif
        </a>

        {{-- ======================== Участницы ======================== --}}
        <x-admin.nav-group group="people" :label="__('Участницы')" :count="$pendingProfiles + $pendingMemberPosts + $managerQuestions" />
        <div class="nav-collapsible" data-group="people" x-init="$nextTick(() => $el.style.maxHeight = '')" :class="open.people ? '' : 'nav-collapsible--closed'">
        <a href="{{ route('admin.profiles.index') }}"
           class="nav-link @if(request()->routeIs('admin.profiles.*')) nav-link--active @endif">
            <x-admin.nav-icon name="users" /><span>{{ __('Профили участниц') }}</span>
            @if($pendingProfiles > 0)<span class="nav-counter" title="{{ __('Ждут решения') }}">{{ $pendingProfiles }}</span>@endif
        </a>
        <a href="{{ route('admin.member-posts.index') }}"
           class="nav-link @if(request()->routeIs('admin.member-posts.*')) nav-link--active @endif">
            <x-admin.nav-icon name="news" /><span>{{ __('Инициативы участниц') }}</span>
            @if($pendingMemberPosts > 0)<span class="nav-counter" title="{{ __('Ждут решения') }}">{{ $pendingMemberPosts }}</span>@endif
        </a>
        <a href="{{ route('admin.manager-questions.index') }}"
           class="nav-link @if(request()->routeIs('admin.manager-questions.*')) nav-link--active @endif">
            <x-admin.nav-icon name="inbox" /><span>{{ __('Вопросы AI-менеджеру') }}</span>
            @if($managerQuestions > 0)<span class="nav-counter" title="{{ __('Вопросы без ответа') }}">{{ $managerQuestions }}</span>@endif
        </a>
        <a href="{{ route('admin.statistics.index') }}"
           class="nav-link @if(request()->routeIs('admin.statistics.*')) nav-link--active @endif">
            <x-admin.nav-icon name="chart" /><span>{{ __('Статистика') }}</span>
        </a>

        {{-- ======================== Пакеты и оплата ======================== --}}
        </div>

        <x-admin.nav-group group="money" :label="__('Пакеты и оплата')" :count="$newPlanRequests + $problemPayments" />
        <div class="nav-collapsible" data-group="money" x-init="$nextTick(() => $el.style.maxHeight = '')" :class="open.money ? '' : 'nav-collapsible--closed'">
        <a href="{{ route('admin.subscriptions.index') }}"
           class="nav-link @if(request()->routeIs('admin.subscriptions.*')) nav-link--active @endif">
            <x-admin.nav-icon name="tag" /><span>{{ __('Пакеты и цены') }}</span>
        </a>
        <a href="{{ route('admin.plan-requests.index') }}"
           class="nav-link @if(request()->routeIs('admin.plan-requests.*')) nav-link--active @endif">
            <x-admin.nav-icon name="users" /><span>{{ __('Заявки на пакет') }}</span>
            @if($newPlanRequests > 0)<span class="nav-counter" title="{{ __('Новые заявки: нужно позвонить') }}">{{ $newPlanRequests }}</span>@endif
        </a>
        <a href="{{ route('admin.payments.index') }}"
           class="nav-link @if(request()->routeIs('admin.payments.*')) nav-link--active @endif">
            <x-admin.nav-icon name="inbox" /><span>{{ __('Платежи') }}</span>
            @if($problemPayments > 0)<span class="nav-counter" title="{{ __('Платежи, которые нужно разобрать вручную') }}">{{ $problemPayments }}</span>@endif
        </a>

        {{-- ======================== Контент ======================== --}}
        </div>

        <x-admin.nav-group group="content" :label="__('Контент')" :count="0" />
        <div class="nav-collapsible" data-group="content" x-init="$nextTick(() => $el.style.maxHeight = '')" :class="open.content ? '' : 'nav-collapsible--closed'">
        {{-- Один пункт на раздел: «Добавить» есть кнопкой в шапке самого списка --}}
        <a href="{{ route('admin.events.index') }}"
           class="nav-link @if(request()->routeIs('admin.events.*')) nav-link--active @endif">
            <x-admin.nav-icon name="event" /><span>{{ __('Новости') }}</span>
        </a>
        <a href="{{ route('admin.opportunities.index') }}"
           class="nav-link @if(request()->routeIs('admin.opportunities.*')) nav-link--active @endif">
            <x-admin.nav-icon name="opportunity" /><span>{{ __('Возможности/обучение') }}</span>
        </a>
        <a href="{{ route('admin.learning-videos.index') }}"
           class="nav-link @if(request()->routeIs('admin.learning-videos.*')) nav-link--active @endif">
            <x-admin.nav-icon name="video" /><span>{{ __('Обучающие видео') }}</span>
        </a>
        <a href="{{ route('admin.projects.index') }}"
           class="nav-link @if(request()->routeIs('admin.projects.*')) nav-link--active @endif">
            <x-admin.nav-icon name="news" /><span>{{ __('Проекты') }}</span>
        </a>
        <a href="{{ route('admin.experts.index') }}"
           class="nav-link @if(request()->routeIs('admin.experts.*')) nav-link--active @endif">
            <x-admin.nav-icon name="users" /><span>{{ __('Эксперты') }}</span>
        </a>

        <x-admin.nav-group group="media" :label="__('Медиатека')" />
        <div class="nav-collapsible" data-group="media" x-init="$nextTick(() => $el.style.maxHeight = '')" :class="open.media ? '' : 'nav-collapsible--closed'">
            <a href="{{ route('admin.albums.index') }}"
               class="nav-sub @if(request()->routeIs('admin.albums.*')) nav-sub--active @endif">
                <x-admin.nav-icon name="album" /><span>{{ __('Фотоальбомы') }}</span>
            </a>
            <a href="{{ route('admin.videos.index') }}"
               class="nav-sub @if(request()->routeIs('admin.videos.*')) nav-sub--active @endif">
                <x-admin.nav-icon name="video" /><span>{{ __('Видео') }}</span>
            </a>
            @if($showPublications)
            <a href="{{ route('admin.posts.index') }}"
               class="nav-sub @if(request()->routeIs('admin.posts.*')) nav-sub--active @endif">
                <x-admin.nav-icon name="news" /><span>{{ __('Публикации') }}</span>
            </a>
            @endif
            <a href="{{ route('admin.catalogs.index') }}"
               class="nav-sub @if(request()->routeIs('admin.catalogs.*')) nav-sub--active @endif">
                <x-admin.nav-icon name="news" /><span>{{ __('Каталоги') }}</span>
            </a>
        </div>

        {{-- ======================== Общение ======================== --}}
        </div>

        <x-admin.nav-group group="comm" :label="__('Общение')" :count="$unreadContacts" />
        <div class="nav-collapsible" data-group="comm" x-init="$nextTick(() => $el.style.maxHeight = '')" :class="open.comm ? '' : 'nav-collapsible--closed'">
        <a href="{{ route('admin.broadcasts.index') }}"
           class="nav-link @if(request()->routeIs('admin.broadcasts.*')) nav-link--active @endif">
            <x-admin.nav-icon name="send" /><span>{{ __('Рассылка в Telegram') }}</span>
        </a>
        <a href="{{ route('admin.contact-messages.index') }}"
           class="nav-link @if(request()->routeIs('admin.contact-messages.*')) nav-link--active @endif">
            <x-admin.nav-icon name="inbox" /><span>{{ __('Обращения') }}</span>
            @if($unreadContacts > 0)<span class="nav-counter" title="{{ __('Непрочитанные обращения') }}">{{ $unreadContacts }}</span>@endif
        </a>
        <a href="{{ route('admin.subscribers.index') }}"
           class="nav-link @if(request()->routeIs('admin.subscribers.*')) nav-link--active @endif">
            <x-admin.nav-icon name="inbox" /><span>{{ __('Подписчики') }}</span>
        </a>
        <a href="{{ route('admin.cabinets.bot-messages') }}"
           class="nav-link @if(request()->routeIs('admin.cabinets.bot-messages*')) nav-link--active @endif">
            <x-admin.nav-icon name="inbox" /><span>{{ __('Сообщения бота') }}</span>
        </a>

        {{-- ======================== Настройки ======================== --}}
        </div>

        <x-admin.nav-group group="settings" :label="__('Настройки')" :count="0" />
        <div class="nav-collapsible" data-group="settings" x-init="$nextTick(() => $el.style.maxHeight = '')" :class="open.settings ? '' : 'nav-collapsible--closed'">
        <a href="{{ route('admin.settings.edit') }}"
           class="nav-link @if(request()->routeIs('admin.settings.*')) nav-link--active @endif">
            <x-admin.nav-icon name="sliders" /><span>{{ __('Настройки сайта') }}</span>
        </a>
        <a href="{{ route('admin.cabinets.settings') }}"
           class="nav-link @if(request()->routeIs('admin.cabinets.settings*')) nav-link--active @endif">
            <x-admin.nav-icon name="sliders" /><span>{{ __('Настройки кабинетов') }}</span>
        </a>
        <a href="{{ route('admin.tags.index') }}"
           class="nav-link @if(request()->routeIs('admin.tags.*')) nav-link--active @endif">
            <x-admin.nav-icon name="tag" /><span>{{ __('Теги') }}</span>
        </a>
        </div>
    </nav>
    {{-- Состояние групп (раскрыта / свёрнута) показывается сразу при разборе страницы, до загрузки Alpine и до первой
         отрисовки: иначе меню мигало бы развёрнутым и сворачивалось потом. Скрипт ставит только временный inline-стиль
         (max-height и поворот стрелки); классы ведёт один Alpine (resources/js/sidebar.js), и сразу после запуска
         x-init снимает временный стиль. Классы здесь ставить нельзя: Alpine не снимет класс, который уже стоял до него,
         и группа не раскроется по клику. Пока скрипт не отработал, меню скрыто (visibility), чтобы не мелькнул промежуточный кадр. Логика состояния та же, что в sidebar.js. --}}
    <script>
        (function () {
            var nav = document.currentScript.previousElementSibling;
            var active = [];
            var saved = null;
            try { active = JSON.parse(nav.getAttribute('data-active') || '[]'); } catch (e) {}
            try { saved = JSON.parse(localStorage.getItem('admin.sidebar.groups.v2') || 'null'); } catch (e) {}

            nav.querySelectorAll('[data-group]').forEach(function (box) {
                var group = box.getAttribute('data-group');
                var open = saved && group in saved ? !!saved[group] : active.indexOf(group) > -1;
                if (active.indexOf(group) > -1) open = true;

                if (!open) box.style.maxHeight = '0px';

                var button = nav.querySelector('[data-group-toggle="' + group + '"]');
                var arrow = button && button.querySelector('svg');
                if (arrow && !open) arrow.style.transform = 'rotate(-90deg)';

                var counter = nav.querySelector('[data-group-counter="' + group + '"]');
                if (counter) counter.style.display = open ? 'none' : '';
            });

            nav.style.visibility = '';
        })();
    </script>
    <noscript><style>.admin-sidebar nav { visibility: visible !important; }</style></noscript>

    <div class="px-4 pt-4 pb-1 border-t border-hairline">
        <x-admin.lang-switch />
    </div>

    <form method="POST" action="{{ route('logout') }}" class="px-4 pb-4 pt-2">
        @csrf
        <button type="submit" class="nav-link w-full !border-l-0 !px-0">
            <x-admin.nav-icon name="logout" /><span>{{ __('Выйти') }}</span>
        </button>
    </form>
</aside>
