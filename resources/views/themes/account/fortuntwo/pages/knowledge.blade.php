@extends('themes.account.fortuntwo.layout')

@section('title', __('account.knowledge.title'))

@section('content')
    <div class="miro-page">
        <header class="miro-page-header">
            <div class="miro-page-header__copy">
                <h1 class="miro-page-title">{{ __('account.knowledge.title') }}</h1>
                <p class="miro-page-description">{{ __('account.knowledge.subtitle') }}</p>
            </div>
        </header>

        @if($cards->isNotEmpty())
            {{-- Текстовые карточки: «Возможности» в админке с галочкой «В кабинете участниц, в «Обучении»». Выглядят как в разделе «Возможности». --}}
            <section aria-labelledby="learning-cards-title" @class(['mb-10' => $videos->isNotEmpty()])>
                @if($videos->isNotEmpty())
                    <h2 id="learning-cards-title" class="lv-title mb-4">{{ __('account.knowledge.materials_title') }}</h2>
                @endif
                <div class="pos-grid">
                    @foreach($cards as $card)
                        <a href="{{ $card['href'] }}" class="pos-card">
                            @if($card['image'])
                                <span class="pos-card__image"><img src="{{ $card['image'] }}" alt="" loading="lazy"></span>
                            @endif
                            <span class="pos-card__body">
                                <span class="pos-card__meta">
                                    @if($card['badge'] !== '')<span class="pos-card__badge">{{ $card['badge'] }}</span>@endif
                                    @if($card['date'] !== '')<span class="pos-card__date">{{ $card['date'] }}</span>@endif
                                </span>
                                <h3 class="pos-card__title">{{ $card['title'] }}</h3>
                                @if($card['excerpt'] !== '')<p class="pos-card__excerpt">{{ $card['excerpt'] }}</p>@endif
                                <span class="pos-card__more">{{ __('account.possibilities.more') }} →</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if($videos->isNotEmpty())
            {{-- Обучающие видео (админка: «Кабинеты участниц → Обучающие видео»). Видео открывается в окне поверх страницы:
                 YouTube без cookie (youtube-nocookie) и без рекомендаций чужих роликов; окно закрывается кнопкой, Esc и щелчком мимо. --}}
            <section x-data="{ video: null }" @keydown.escape.window="video = null" aria-labelledby="learning-videos-title">
                <h2 id="learning-videos-title" class="lv-title">{{ __('account.knowledge.videos_title') }}</h2>
                <p class="lv-hint">{{ __('account.knowledge.videos_hint') }}</p>

                <div class="lv-grid">
                    @foreach($videos as $video)
                        @php
                            $title = $video->field('title') ?: $video->youtube_id;
                            $description = $video->field('description');
                        @endphp
                        <article class="lv-card">
                            <button type="button" class="lv-card__cover" aria-label="{{ __('account.knowledge.watch') }}: {{ $title }}"
                                    data-url="{{ $video->embedUrl() }}" data-title="{{ $title }}"
                                    @click="video = { url: $el.dataset.url, title: $el.dataset.title }">
                                <img src="{{ $video->coverUrl() }}" alt="" loading="lazy">
                                <span class="lv-card__play" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M8 5.5v13l11-6.5z"/></svg>
                                </span>
                            </button>
                            <div class="lv-card__body">
                                <h3 class="lv-card__title">{{ $title }}</h3>
                                @if(filled($description))<p class="lv-card__text">{{ $description }}</p>@endif
                            </div>
                        </article>
                    @endforeach
                </div>

                <div x-show="video" x-cloak class="lv-modal" role="dialog" aria-modal="true" :aria-label="video ? video.title : ''" @click.self="video = null">
                    <div class="lv-modal__box">
                        <button type="button" class="lv-modal__close" @click="video = null" aria-label="{{ __('account.knowledge.close') }}">&times;</button>
                        <div class="lv-modal__frame">
                            {{-- Плеер создаётся только при открытии окна и удаляется при закрытии: звук не продолжается за кадром --}}
                            <template x-if="video">
                                <iframe :src="video.url" :title="video.title" allow="autoplay; encrypted-media; picture-in-picture; fullscreen" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
                            </template>
                        </div>
                        <p class="lv-modal__caption" x-text="video ? video.title : ''"></p>
                    </div>
                </div>
            </section>
        @elseif($cards->isEmpty())
            <div class="miro-card miro-card--pink p-6 sm:p-10">
                <div class="grid gap-10 lg:grid-cols-[1.1fr_.9fr] lg:items-center">
                    <div>
                        <span class="inline-flex rounded-full bg-white/75 px-4 py-2 text-xs font-medium text-[#21140F]">{{ __('account.nav.knowledge') }}</span>
                        <h2 class="mt-6 max-w-xl text-3xl font-medium tracking-tight text-[#21140F] sm:text-4xl" style="font-family: var(--fortun-display-font); font-weight: 400;">{{ __('account.knowledge.coming_title') }}</h2>
                        <p class="mt-4 max-w-xl text-sm leading-7 text-[#21140F]/75">{{ __('account.knowledge.coming_text') }}</p>
                        <a href="{{ route('account.opportunities.index') }}" class="miro-button miro-button--dark mt-7">{{ __('account.nav.opportunities') }}</a>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        @foreach(__('account.knowledge.modules') as $index => $module)
                            <div class="rounded-[28px] border border-white/70 bg-white/65 p-5">
                                <div class="mb-8 flex h-10 w-10 items-center justify-center rounded-2xl bg-[#FAEEE8] text-[#21140F] text-sm font-medium">0{{ $index + 1 }}</div>
                                <p class="font-medium text-[#21140F]">{{ $module }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
