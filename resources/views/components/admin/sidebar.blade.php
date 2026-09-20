@php
    use App\Support\AdminCamp;

    // Лагерь определяется маршрутом: под переключателем показывается меню только его.
    $camp = AdminCamp::current();

    // «Добавить …» подсвечивается, когда открыта форма создания; список —
    // на index и на редактировании.
    $isCreate = fn (string $name) => request()->routeIs($name);
    $isList = fn (string $prefix) => request()->routeIs($prefix.'.index') || request()->routeIs($prefix.'.edit');

    // Группа с активной страницей раскрывается принудительно (группы есть только у «Внешнего сайта»).
    // «Медиатека» объединяет фотоальбомы, видео и публикации (каталоги и брошюры организации),
    // как и в меню публичного сайта.
    $activeGroup = match (true) {
        request()->routeIs('admin.events.*') => 'events',
        request()->routeIs('admin.projects.*') => 'projects',
        request()->routeIs('admin.opportunities.*') => 'opportunities',
        request()->routeIs('admin.experts.*') => 'experts',
        request()->routeIs('admin.albums.*'), request()->routeIs('admin.videos.*'), request()->routeIs('admin.posts.*') => 'media',
        default => null,
    };

    // Сколько профилей и постов участниц ждут решения: сумма стоит на вкладке «Кабинеты участниц»
    // и видна из любого раздела сайта, чтобы очередь модерации не потерялась.
    $pendingProfiles = \App\Models\BotUser::pending()->count();
    $pendingMemberPosts = \App\Models\Opportunity::pending()->count();
@endphp

<aside {{ $attributes->merge(['class' => 'admin-sidebar']) }}>
    <a href="{{ route('admin.dashboard') }}"
       class="h-header flex items-center gap-2.5 px-4 text-on-sidebar font-semibold border-b border-hairline shrink-0"
       title="{{ config('app.name') }}">
        <img src="{{ asset('themes/public/miro/images/brand/favicon.png') }}" alt="{{ config('app.name') }}" class="h-8 w-8 shrink-0">
        <span>Админпанель</span>
    </a>

    <x-admin.camp-switch :camp="$camp" :pending="$pendingProfiles + $pendingMemberPosts" />

    <nav class="py-2 flex-1" x-data="sidebarNav(@js($activeGroup))">
        @if($camp === AdminCamp::CABINETS)
            {{-- ===================== Кабинеты участниц ===================== --}}
            <a href="{{ route('admin.cabinets.dashboard') }}"
               class="nav-link @if(request()->routeIs('admin.cabinets.dashboard')) nav-link--active @endif">
                <x-admin.nav-icon name="dashboard" /><span>Инфопанель</span>
            </a>

            <a href="{{ route('admin.profiles.index') }}"
               class="nav-link mt-2 @if(request()->routeIs('admin.profiles.*')) nav-link--active @endif">
                <x-admin.nav-icon name="users" /><span>Профили участниц</span>
                @if($pendingProfiles > 0)<span class="nav-counter">{{ $pendingProfiles }}</span>@endif
            </a>
            <a href="{{ route('admin.member-posts.index') }}"
               class="nav-link @if(request()->routeIs('admin.member-posts.*')) nav-link--active @endif">
                <x-admin.nav-icon name="news" /><span>Посты участниц</span>
                @if($pendingMemberPosts > 0)<span class="nav-counter">{{ $pendingMemberPosts }}</span>@endif
            </a>
            <a href="{{ route('admin.statistics.index') }}"
               class="nav-link @if(request()->routeIs('admin.statistics.*')) nav-link--active @endif">
                <x-admin.nav-icon name="chart" /><span>Статистика</span>
            </a>

            <a href="{{ route('admin.cabinets.settings') }}"
               class="nav-link mt-2 @if(request()->routeIs('admin.cabinets.settings*')) nav-link--active @endif">
                <x-admin.nav-icon name="sliders" /><span>Настройки кабинетов</span>
            </a>
        @else
            {{-- ======================== Внешний сайт ======================== --}}
            <a href="{{ route('admin.dashboard') }}"
               class="nav-link @if(request()->routeIs('admin.dashboard')) nav-link--active @endif">
                <x-admin.nav-icon name="dashboard" /><span>Инфопанель</span>
            </a>

            <x-admin.nav-group group="events" label="Новости" />
            <div class="nav-collapsible" :class="open.events ? '' : 'nav-collapsible--closed'">
                <a href="{{ route('admin.events.index') }}"
                   class="nav-sub @if($isList('admin.events')) nav-sub--active @endif">
                    <x-admin.nav-icon name="event" /><span>Все новости</span>
                </a>
                <a href="{{ route('admin.events.create') }}"
                   class="nav-sub @if($isCreate('admin.events.create')) nav-sub--active @endif">
                    <x-admin.nav-icon name="plus" /><span>Добавить новость</span>
                </a>
            </div>

            <x-admin.nav-group group="projects" label="Проекты" />
            <div class="nav-collapsible" :class="open.projects ? '' : 'nav-collapsible--closed'">
                <a href="{{ route('admin.projects.index') }}"
                   class="nav-sub @if($isList('admin.projects')) nav-sub--active @endif">
                    <x-admin.nav-icon name="news" /><span>Все проекты</span>
                </a>
                <a href="{{ route('admin.projects.create') }}"
                   class="nav-sub @if($isCreate('admin.projects.create')) nav-sub--active @endif">
                    <x-admin.nav-icon name="plus" /><span>Добавить проект</span>
                </a>
            </div>

            <x-admin.nav-group group="opportunities" label="Возможности" />
            <div class="nav-collapsible" :class="open.opportunities ? '' : 'nav-collapsible--closed'">
                <a href="{{ route('admin.opportunities.index') }}"
                   class="nav-sub @if($isList('admin.opportunities')) nav-sub--active @endif">
                    <x-admin.nav-icon name="opportunity" /><span>Все возможности</span>
                </a>
                <a href="{{ route('admin.opportunities.create') }}"
                   class="nav-sub @if($isCreate('admin.opportunities.create')) nav-sub--active @endif">
                    <x-admin.nav-icon name="plus" /><span>Добавить возможность</span>
                </a>
            </div>

            <x-admin.nav-group group="experts" label="Эксперты" />
            <div class="nav-collapsible" :class="open.experts ? '' : 'nav-collapsible--closed'">
                <a href="{{ route('admin.experts.index') }}"
                   class="nav-sub @if($isList('admin.experts')) nav-sub--active @endif">
                    <x-admin.nav-icon name="users" /><span>Все эксперты</span>
                </a>
                <a href="{{ route('admin.experts.create') }}"
                   class="nav-sub @if($isCreate('admin.experts.create')) nav-sub--active @endif">
                    <x-admin.nav-icon name="plus" /><span>Добавить эксперта</span>
                </a>
            </div>

            <x-admin.nav-group group="media" label="Медиатека" />
            <div class="nav-collapsible" :class="open.media ? '' : 'nav-collapsible--closed'">
                <a href="{{ route('admin.albums.index') }}"
                   class="nav-sub @if($isList('admin.albums')) nav-sub--active @endif">
                    <x-admin.nav-icon name="album" /><span>Все фотоальбомы</span>
                </a>
                <a href="{{ route('admin.albums.create') }}"
                   class="nav-sub @if($isCreate('admin.albums.create')) nav-sub--active @endif">
                    <x-admin.nav-icon name="plus" /><span>Добавить фотоальбом</span>
                </a>
                <a href="{{ route('admin.videos.index') }}"
                   class="nav-sub @if($isList('admin.videos')) nav-sub--active @endif">
                    <x-admin.nav-icon name="video" /><span>Все видео</span>
                </a>
                <a href="{{ route('admin.videos.create') }}"
                   class="nav-sub @if($isCreate('admin.videos.create')) nav-sub--active @endif">
                    <x-admin.nav-icon name="plus" /><span>Добавить видео</span>
                </a>
                <a href="{{ route('admin.posts.index') }}"
                   class="nav-sub @if($isList('admin.posts')) nav-sub--active @endif">
                    <x-admin.nav-icon name="news" /><span>Все публикации</span>
                </a>
                <a href="{{ route('admin.posts.create') }}"
                   class="nav-sub @if($isCreate('admin.posts.create')) nav-sub--active @endif">
                    <x-admin.nav-icon name="plus" /><span>Добавить публикацию</span>
                </a>
            </div>

            <a href="{{ route('admin.tags.index') }}"
               class="nav-link mt-2 @if(request()->routeIs('admin.tags.*')) nav-link--active @endif">
                <x-admin.nav-icon name="tag" /><span>Теги</span>
            </a>

            <a href="{{ route('admin.subscribers.index') }}"
               class="nav-link @if(request()->routeIs('admin.subscribers.*')) nav-link--active @endif">
                <x-admin.nav-icon name="inbox" /><span>Подписчики</span>
            </a>

            <a href="{{ route('admin.settings.edit') }}"
               class="nav-link @if(request()->routeIs('admin.settings.*')) nav-link--active @endif">
                <x-admin.nav-icon name="sliders" /><span>Настройки сайта</span>
            </a>
        @endif
    </nav>

    <form method="POST" action="{{ route('logout') }}" class="p-4 border-t border-hairline">
        @csrf
        <button type="submit" class="nav-link w-full !border-l-0 !px-0">
            <x-admin.nav-icon name="logout" /><span>Выйти</span>
        </button>
    </form>
</aside>
