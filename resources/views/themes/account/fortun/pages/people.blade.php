@extends('themes.account.fortun.layout')

@section('title', __('account.people.title'))

@php
    $locale = app()->getLocale();
    $profileMeta = [
        [
            'role' => ['ru' => 'Участница', 'en' => 'Member', 'ro' => 'Participantă'],
            'specialization' => ['ru' => 'Мода и дизайн', 'en' => 'Fashion & design', 'ro' => 'Modă și design'],
            'offer' => ['ru' => 'Дизайн продукта и работа с визуальным стилем.', 'en' => 'Product design and visual direction.', 'ro' => 'Design de produs și direcție vizuală.'],
            'tags' => [
                ['tone' => 'pink', 'ru' => 'Участница', 'en' => 'Member', 'ro' => 'Participantă'],
                ['tone' => 'rose', 'ru' => 'Дизайн', 'en' => 'Design', 'ro' => 'Design'],
                ['tone' => 'teal', 'ru' => 'Ищет партнёра', 'en' => 'Looking for a partner', 'ro' => 'Caută partener'],
            ],
        ],
        [
            'role' => ['ru' => 'Эксперт', 'en' => 'Expert', 'ro' => 'Expertă'],
            'specialization' => ['ru' => 'Маркетинг и цифровые услуги', 'en' => 'Marketing & digital services', 'ro' => 'Marketing și servicii digitale'],
            'offer' => ['ru' => 'Маркетинг, продвижение и цифровая стратегия.', 'en' => 'Marketing, promotion and digital strategy.', 'ro' => 'Marketing, promovare și strategie digitală.'],
            'tags' => [
                ['tone' => 'coral', 'ru' => 'Эксперт', 'en' => 'Expert', 'ro' => 'Expertă'],
                ['tone' => 'blue', 'ru' => 'Маркетолог', 'en' => 'Marketing', 'ro' => 'Marketing'],
                ['tone' => 'pink', 'ru' => 'Предлагает услуги', 'en' => 'Offers services', 'ro' => 'Oferă servicii'],
            ],
        ],
        [
            'role' => ['ru' => 'Участница', 'en' => 'Member', 'ro' => 'Participantă'],
            'specialization' => ['ru' => 'Агро и продукты', 'en' => 'Agri & food', 'ro' => 'Agri și produse'],
            'offer' => ['ru' => 'Локальный продукт и опыт устойчивого развития.', 'en' => 'A local product and sustainable growth experience.', 'ro' => 'Produs local și experiență în dezvoltare durabilă.'],
            'tags' => [
                ['tone' => 'teal', 'ru' => 'Участница', 'en' => 'Member', 'ro' => 'Participantă'],
                ['tone' => 'orange', 'ru' => 'Агро и продукты', 'en' => 'Agri & food', 'ro' => 'Agri și produse'],
                ['tone' => 'rose', 'ru' => 'Ищет новые рынки', 'en' => 'Looking for new markets', 'ro' => 'Caută piețe noi'],
            ],
        ],
        [
            'role' => ['ru' => 'Эксперт', 'en' => 'Expert', 'ro' => 'Expertă'],
            'specialization' => ['ru' => 'Развитие сообщества', 'en' => 'Community building', 'ro' => 'Dezvoltarea comunității'],
            'offer' => ['ru' => 'Менторство, связи и поддержка предпринимательниц.', 'en' => 'Mentorship, connections and support for entrepreneurs.', 'ro' => 'Mentorat, conexiuni și sprijin pentru antreprenoare.'],
            'tags' => [
                ['tone' => 'rose', 'ru' => 'Эксперт', 'en' => 'Expert', 'ro' => 'Expertă'],
                ['tone' => 'teal', 'ru' => 'Менторство', 'en' => 'Mentorship', 'ro' => 'Mentorat'],
                ['tone' => 'coral', 'ru' => 'Открыта к сотрудничеству', 'en' => 'Open to collaboration', 'ro' => 'Deschisă colaborării'],
            ],
        ],
    ];

    $contactLabel = [
        'ru' => 'Связаться в Telegram',
        'en' => 'Contact via Telegram',
        'ro' => 'Contactează prin Telegram',
    ][$locale] ?? 'Contact via Telegram';
@endphp

@section('content')
    <div class="miro-directory-page">
        <header class="miro-directory-header">
            <p class="miro-directory-header__eyebrow">{{ __('account.nav.people') }}</p>
            <h1>{{ __('account.people.title') }}</h1>
            <p>{{ __('account.people.subtitle') }}</p>
        </header>

        <div class="miro-actions mb-7">
            <a href="{{ route('account.search') }}" class="miro-button miro-button--dark">{{ __('account.dashboard.action_search') }}</a>
            <a href="{{ route('account.profile') }}" class="miro-button miro-button--outline">{{ __('account.nav.profile') }}</a>
        </div>

        @if($people->isEmpty())
            <div class="miro-empty">
                <span class="miro-empty__mark"><svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m7-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7-4v6m3-3h-6"/></svg></span>
                <h2>{{ __('account.people.empty_title') }}</h2>
                <p>{{ __('account.people.empty_text') }}</p>
            </div>
        @else
            <div class="miro-directory-grid">
                @foreach($people as $person)
                    @php($meta = $profileMeta[$loop->index % count($profileMeta)])
                    <article class="miro-directory-card">
                        <div class="miro-directory-card__profile">
                            <div class="miro-directory-card__avatar">
                                @if($person->avatar_path)
                                    <img src="{{ Storage::url($person->avatar_path) }}" alt="{{ $person->full_name ?: __('account.people.title') }}" loading="lazy">
                                @else
                                    <div class="miro-directory-card__avatar-placeholder">
                                        {{ mb_strtoupper(mb_substr($person->full_name ?: $person->telegram_username ?: '?', 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="miro-directory-card__role">{{ $meta['role'][$locale] ?? $meta['role']['ru'] }}</p>
                                <h2>{{ $person->full_name ?: __('account.not_specified') }}</h2>
                                <p class="miro-directory-card__specialization">{{ $meta['specialization'][$locale] ?? $meta['specialization']['ru'] }}</p>
                            </div>
                        </div>

                        <div class="miro-directory-card__tags">
                            @foreach($meta['tags'] as $tag)
                                <span class="miro-directory-card__tag miro-directory-card__tag--{{ $tag['tone'] }}">
                                    {{ $tag[$locale] ?? $tag['ru'] }}
                                </span>
                            @endforeach
                        </div>

                        <p class="miro-directory-card__description">
                            {{ $person->description ?: __('account.not_filled') }}
                        </p>

                        <div class="miro-directory-card__details">
                            <div class="miro-directory-card__detail">
                                <span class="miro-directory-card__detail-label">
                                    {{ ['ru' => 'Ищет', 'en' => 'Looking for', 'ro' => 'Caută'][$locale] ?? 'Looking for' }}
                                </span>
                                <p>{{ $person->expectation ?: __('account.not_filled') }}</p>
                            </div>
                            <div class="miro-directory-card__detail miro-directory-card__detail--offer">
                                <span class="miro-directory-card__detail-label">
                                    {{ ['ru' => 'Может предложить', 'en' => 'Can offer', 'ro' => 'Poate oferi'][$locale] ?? 'Can offer' }}
                                </span>
                                <p>{{ $meta['offer'][$locale] ?? $meta['offer']['ru'] }}</p>
                            </div>
                        </div>

                        @if($person->telegram_username)
                            <a href="https://t.me/{{ $person->telegram_username }}" target="_blank" rel="noopener" class="miro-directory-card__contact">
                                {{ $contactLabel }}
                            </a>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </div>
@endsection

