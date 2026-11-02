{{--
    Главная кабинета (account.index). Сверху имя, пакет со сроком и заполненность профиля, затем короткие плитки разделов
    (навигация), рекомендации, «Что нового» и ближайшие события горизонтальными лентами. Community видит меньше
    (рекомендации и участницы с Active) и приглашение улучшить пакет. При первом входе поверх страницы идут подсказки
    («Далее» / «Закрыть», account/partials/tour.blade.php), потом их можно открыть снова.
    Переменные: App\Support\CabinetHome::build() (name, paid, plan, profile, tiles, recs, news, events, locked), $user и $showTour.
--}}
@extends('themes.account.'.($accountTheme ?? \App\Models\SiteSetting::DEFAULT_ACCOUNT_THEME).'.layout')

@section('title', __('account.nav.home'))

@section('notices_inline', '1')

@section('content')
<div class="account-home mx-auto max-w-6xl">
    @include('account.partials.notices')

    {{-- Приветствие, пакет и заполненность профиля --}}
    <section class="home-hero mb-6" aria-label="{{ __('account.home.eyebrow') }}">
        <div class="home-hero__main">
            <p class="text-xs font-semibold uppercase tracking-widest text-brand-600">{{ __('account.home.eyebrow') }}</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight sm:text-3xl">{{ __('account.home.hello', ['name' => $name]) }}</h1>
            <button type="button" data-tour-start class="mt-2 text-sm font-semibold text-brand-700 hover:underline">{{ __('account.tour.replay') }}</button>
        </div>

        <div class="home-hero__cards">
            @if($plan['active'])
                <div class="home-stat">
                    <p class="home-stat__label">{{ __('account.home.your_plan') }}</p>
                    <p class="home-stat__value">{{ $plan['title'] }}</p>
                    <p class="home-stat__hint">
                        @if($plan['ends_at']){{ __('account.home.plan_until', ['date' => $plan['ends_at']]) }}@endif
                    </p>
                    @if($plan['renew'])
                        <a href="{{ route('account.subscription') }}" class="home-stat__link">{{ __('account.home.renew') }} →</a>
                    @endif
                </div>
            @endif
            <div class="home-stat">
                <p class="home-stat__label">{{ __('account.home.profile_filled') }}</p>
                <p class="home-stat__value">{{ $profile['percent'] }}%</p>
                <div class="home-meter" role="progressbar" aria-valuenow="{{ $profile['percent'] }}" aria-valuemin="0" aria-valuemax="100"><span style="width: {{ $profile['percent'] }}%"></span></div>
                @if($profile['next'])
                    <a href="{{ route('account.profile.edit') }}" class="home-stat__link">{{ __('account.home.profile_missing.'.$profile['next']) }} →</a>
                @else
                    <p class="home-stat__hint">{{ __('account.home.profile_done') }}</p>
                @endif
            </div>
        </div>
    </section>

    {{-- Разделы: короткие плитки для быстрого перехода (закрытые для пакета с замком) --}}
    <nav class="mb-8" aria-label="{{ __('account.home.tiles_title') }}">
        <div class="home-tiles">
            @foreach($tiles as $tile)
                <a href="{{ route($tile['route']) }}" class="home-tile {{ $tile['locked'] ? 'home-tile--locked' : '' }}" @if($tile['locked']) title="{{ __('account.home.tile_locked') }}" @endif>
                    <span class="home-tile__icon">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $tile['path'] }}"/></svg>
                    </span>
                    <span class="home-tile__label">{{ $tile['label'] }}</span>
                    @if($tile['locked'])
                        <svg class="home-tile__lock h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 10-8 0v4m-1 0h10a1 1 0 011 1v7a1 1 0 01-1 1H7a1 1 0 01-1-1v-7a1 1 0 011-1z"/></svg>
                    @elseif(! empty($tile['premium']))
                        <span class="account-premium-tag">PREMIUM</span>
                    @endif
                </a>
            @endforeach
        </div>
    </nav>

    {{-- Мои рекомендации (с Active) --}}
    @if($recs !== null)
        <section class="home-section" aria-labelledby="home-recs-title">
            <div class="home-section__head">
                <h2 id="home-recs-title" class="home-section__title">{{ __('account.home.recs_title') }}</h2>
                <a href="{{ route('account.matches') }}" class="home-section__more">{{ __('account.home.all_recs') }} →</a>
            </div>
            @if($recs['people'] === [])
                <div class="home-empty">
                    <p>{{ __('account.home.recs_empty') }}</p>
                    <a href="{{ route('account.profile.edit') }}" class="home-stat__link">{{ __('account.profile.edit') }} →</a>
                </div>
            @else
                <div class="home-rail">
                    @foreach($recs['people'] as $person)
                        <a href="{{ $person['href'] }}" class="home-card home-card--person">
                            <span class="home-avatar">
                                @if($person['avatar'])<img src="{{ $person['avatar'] }}" alt="" loading="lazy">@else<span>{{ $person['initial'] }}</span>@endif
                            </span>
                            <span class="home-card__body">
                                <span class="home-card__kicker">{{ __('account.matches.score') }} · {{ $person['score'] }}%</span>
                                <span class="home-card__title">{{ $person['name'] }}</span>
                                @if($person['text'] !== '')<span class="home-card__text">{{ $person['text'] }}</span>@endif
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>
    @endif

    {{-- Что нового --}}
    @if($news !== [])
        <section class="home-section" aria-labelledby="home-news-title">
            <div class="home-section__head">
                <h2 id="home-news-title" class="home-section__title">{{ __('account.home.news_title') }}</h2>
            </div>
            <div class="home-rail">
                @foreach($news as $item)
                    <a href="{{ $item['href'] }}" class="home-card">
                        @if($item['image'])
                            <span class="home-card__image"><img src="{{ $item['image'] }}" alt="" loading="lazy"></span>
                        @endif
                        <span class="home-card__body">
                            <span class="home-card__kicker">{{ $item['kind'] }}@if(($item['date'] ?? '') !== '') · {{ $item['date'] }}@endif</span>
                            <span class="home-card__title">{{ $item['title'] }}</span>
                            @if(($item['excerpt'] ?? '') !== '')<span class="home-card__text">{{ $item['excerpt'] }}</span>@endif
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Ближайшие события --}}
    @if($events !== [])
        <section class="home-section" aria-labelledby="home-events-title">
            <div class="home-section__head">
                <h2 id="home-events-title" class="home-section__title">{{ __('account.home.events_title') }}</h2>
            </div>
            <div class="home-rail">
                @foreach($events as $event)
                    @if($event['href'])
                        <a href="{{ $event['href'] }}" class="home-card" @if($event['external']) target="_blank" rel="noopener noreferrer" @endif>
                    @else
                        <div class="home-card">
                    @endif
                        @if($event['image'])
                            <span class="home-card__image"><img src="{{ $event['image'] }}" alt="" loading="lazy"></span>
                        @endif
                        <span class="home-card__body">
                            <span class="home-card__kicker">{{ $event['date'] }}@if($event['place'] !== '') · {{ $event['place'] }}@endif</span>
                            <span class="home-card__title">{{ $event['title'] }}</span>
                        </span>
                    @if($event['href'])
                        </a>
                    @else
                        </div>
                    @endif
                @endforeach
            </div>
        </section>
    @endif

    {{-- Community: что откроет Active --}}
    @if($plan['active'] && ! $paid)
        <section class="home-upgrade" aria-labelledby="home-upgrade-title">
            <div>
                <h2 id="home-upgrade-title" class="home-section__title">{{ __('account.home.upgrade_title') }}</h2>
                <p class="mt-1 text-sm leading-6 text-gray-600">{{ __('account.home.upgrade_text') }}</p>
                @if($locked !== [])
                    <ul class="home-upgrade__list">
                        @foreach($locked as $label)<li>{{ $label }}</li>@endforeach
                    </ul>
                @endif
            </div>
            <a href="{{ route('account.subscription') }}" class="miro-button miro-button--dark shrink-0">{{ __('account.home.upgrade_cta') }}</a>
        </section>
    @endif
</div>
{{-- Ленты листаются стрелками: кнопки добавляются к каждой ленте, у края ленты нужная стрелка гаснет; свайп на телефоне остаётся --}}
<script>
(function () {
    var labels = @json(['prev' => __('account.home.rail_prev'), 'next' => __('account.home.rail_next')]);

    document.querySelectorAll('.account-home .home-rail').forEach(function (rail) {
        var wrap = document.createElement('div');
        wrap.className = 'home-rail-wrap';
        rail.parentNode.insertBefore(wrap, rail);

        function button(dir, label, path) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'home-rail-btn home-rail-btn--' + dir;
            b.setAttribute('aria-label', label);
            b.innerHTML = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="' + path + '"/></svg>';
            return b;
        }

        var prev = button('prev', labels.prev, 'M15 19l-7-7 7-7');
        var next = button('next', labels.next, 'M9 5l7 7-7 7');

        wrap.appendChild(prev);
        wrap.appendChild(rail);
        wrap.appendChild(next);
        rail.classList.add('home-rail--arrows');

        function update() {
            var max = rail.scrollWidth - rail.clientWidth;
            prev.disabled = rail.scrollLeft <= 2;
            next.disabled = rail.scrollLeft >= max - 2;
            wrap.classList.toggle('home-rail-wrap--static', max <= 2);
        }

        function step(dir) {
            var card = rail.querySelector('.home-card');
            var gap = 14;
            var width = card ? card.getBoundingClientRect().width + gap : rail.clientWidth * 0.8;
            var count = Math.max(1, Math.floor(rail.clientWidth / width));
            rail.scrollBy({ left: dir * width * count, behavior: 'smooth' });
        }

        prev.addEventListener('click', function () { step(-1); });
        next.addEventListener('click', function () { step(1); });
        rail.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update);
        update();
    });
})();
</script>
@include('account.partials.tour',['tourSteps' => \App\Support\CabinetTour::steps($user), 'replaySteps' => \App\Support\CabinetTour::steps($user, true), 'autoStart' => $showTour ?? false])
@endsection
