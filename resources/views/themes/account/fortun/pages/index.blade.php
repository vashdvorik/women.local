@extends('themes.account.fortun.layout')
@section('title', __('account.dashboard.title'))

@php
    $locale = app()->getLocale();
    $aiCopy = [
        'ru' => [
            'label' => 'Fortun AI',
            'mode' => 'Тестовый режим',
            'title' => 'ИИ-помощник платформы',
            'intro' => 'Помогу разобраться, как устроен кабинет, и подскажу следующий шаг.',
            'welcome' => 'Привет! Я помогу найти нужный раздел, разобраться с профилем и понять, как использовать возможности платформы.',
            'question' => 'Как найти подходящих партнёров?',
            'answer' => 'Заполните профиль и откройте раздел «Профили платформы». Позже я смогу подбирать контакты по вашим интересам и запросам.',
            'prompt_one' => 'Как работает платформа?',
            'prompt_two' => 'Как найти эксперта?',
            'prompt_three' => 'Что можно добавить в профиль?',
            'placeholder' => 'Напишите вопрос помощнику',
            'send' => 'Отправить',
            'profile_title' => 'Ваша визитка',
            'profile_text' => 'Так вас видят участницы платформы. Чем точнее описание, тем полезнее будут будущие рекомендации.',
            'profile_public' => 'Профиль виден участницам',
        ],
        'en' => [
            'label' => 'Fortun AI',
            'mode' => 'Demo mode',
            'title' => 'Your platform AI assistant',
            'intro' => 'I will explain how the account works and suggest the next step.',
            'welcome' => 'Hi! I can help you find the right section, understand your profile and explore the platform step by step.',
            'question' => 'How can I find the right partners?',
            'answer' => 'Complete your profile and open Platform profiles. Later, I will be able to suggest contacts based on your interests and needs.',
            'prompt_one' => 'How does the platform work?',
            'prompt_two' => 'How can I find an expert?',
            'prompt_three' => 'What can I add to my profile?',
            'placeholder' => 'Ask the assistant a question',
            'send' => 'Send',
            'profile_title' => 'Your business card',
            'profile_text' => 'This is how platform participants see you. The clearer your profile, the more useful future recommendations will be.',
            'profile_public' => 'Visible to participants',
        ],
        'ro' => [
            'label' => 'Fortun AI',
            'mode' => 'Mod demo',
            'title' => 'Asistentul AI al platformei',
            'intro' => 'Vă explic cum funcționează cabinetul și vă sugerez următorul pas.',
            'welcome' => 'Bună! Vă pot ajuta să găsiți secțiunea potrivită, să înțelegeți profilul și să descoperiți platforma pas cu pas.',
            'question' => 'Cum pot găsi partenerii potriviți?',
            'answer' => 'Completați profilul și deschideți Profilurile platformei. Mai târziu, voi putea recomanda contacte după interesele și nevoile dvs.',
            'prompt_one' => 'Cum funcționează platforma?',
            'prompt_two' => 'Cum găsesc un expert?',
            'prompt_three' => 'Ce pot adăuga în profil?',
            'placeholder' => 'Scrieți o întrebare pentru asistent',
            'send' => 'Trimite',
            'profile_title' => 'Cartea dvs. de vizită',
            'profile_text' => 'Așa vă văd participantele platformei. Cu cât profilul este mai clar, cu atât recomandările viitoare vor fi mai utile.',
            'profile_public' => 'Vizibil pentru participante',
        ],
    ][$locale] ?? [
        'label' => 'Fortun AI',
        'mode' => 'Demo mode',
        'title' => 'Your platform AI assistant',
        'intro' => 'I will explain how the account works and suggest the next step.',
        'welcome' => 'Hi! I can help you find the right section, understand your profile and explore the platform step by step.',
        'question' => 'How can I find the right partners?',
        'answer' => 'Complete your profile and open Platform profiles. Later, I will be able to suggest contacts based on your interests and needs.',
        'prompt_one' => 'How does the platform work?',
        'prompt_two' => 'How can I find an expert?',
        'prompt_three' => 'What can I add to my profile?',
        'placeholder' => 'Ask the assistant a question',
        'send' => 'Send',
        'profile_title' => 'Your business card',
        'profile_text' => 'This is how platform participants see you. The clearer your profile, the more useful future recommendations will be.',
        'profile_public' => 'Visible to participants',
    ];
@endphp

@section('content')
<div class="fortun-dashboard-page">
    <header class="fortun-dashboard-header">
        <div class="fortun-dashboard-header__copy">
            <p class="miro-eyebrow">{{ __('account.dashboard.eyebrow') }}</p>
            <h1 class="fortun-dashboard-header__title">{{ __('account.dashboard.hello', ['name' => explode(' ', (string) $accountUser->full_name)[0]]) }}</h1>
            <p class="fortun-dashboard-header__description">{{ __('account.dashboard.intro') }}</p>
        </div>
    </header>

    <section class="fortun-ai-assistant" aria-labelledby="fortun-ai-title">
        <span class="fortun-ai-assistant__orb fortun-ai-assistant__orb--one" aria-hidden="true"></span>
        <span class="fortun-ai-assistant__orb fortun-ai-assistant__orb--two" aria-hidden="true"></span>
        <span class="fortun-ai-assistant__ring" aria-hidden="true"></span>
        <span class="fortun-ai-assistant__spark fortun-ai-assistant__spark--one" aria-hidden="true"></span>
        <span class="fortun-ai-assistant__spark fortun-ai-assistant__spark--two" aria-hidden="true"></span>

        <div class="fortun-ai-assistant__header">
            <div class="fortun-ai-assistant__identity">
                <span class="fortun-ai-assistant__mark" aria-hidden="true">AI</span>
                <div>
                    <div class="fortun-ai-assistant__label-row">
                        <p class="fortun-ai-assistant__label">{{ $aiCopy['label'] }}</p>
                        <span class="fortun-ai-assistant__mode">{{ $aiCopy['mode'] }}</span>
                    </div>
                    <h2 id="fortun-ai-title" class="fortun-ai-assistant__title">{{ $aiCopy['title'] }}</h2>
                    <p class="fortun-ai-assistant__intro">{{ $aiCopy['intro'] }}</p>
                </div>
            </div>
        </div>

        <div class="fortun-ai-chat" aria-label="{{ $aiCopy['label'] }}">
            <div class="fortun-ai-message fortun-ai-message--assistant">
                <span class="fortun-ai-message__author">{{ $aiCopy['label'] }}</span>
                <p>{{ $aiCopy['welcome'] }}</p>
            </div>
            <div class="fortun-ai-message fortun-ai-message--user">
                <span class="fortun-ai-message__author">{{ __('account.profile.title') }}</span>
                <p>{{ $aiCopy['question'] }}</p>
            </div>
            <div class="fortun-ai-message fortun-ai-message--assistant">
                <span class="fortun-ai-message__author">{{ $aiCopy['label'] }}</span>
                <p>{{ $aiCopy['answer'] }}</p>
            </div>
        </div>

        <div class="fortun-ai-prompts" aria-label="{{ $aiCopy['label'] }}">
            <button type="button" class="fortun-ai-prompt">{{ $aiCopy['prompt_one'] }}</button>
            <button type="button" class="fortun-ai-prompt">{{ $aiCopy['prompt_two'] }}</button>
            <button type="button" class="fortun-ai-prompt">{{ $aiCopy['prompt_three'] }}</button>
        </div>

        <form class="fortun-ai-composer" onsubmit="return false">
            <input type="text" readonly placeholder="{{ $aiCopy['placeholder'] }}" aria-label="{{ $aiCopy['placeholder'] }}">
            <button type="button" class="fortun-ai-composer__send">{{ $aiCopy['send'] }}</button>
        </form>
    </section>

    <section class="fortun-dashboard-profile" aria-labelledby="fortun-dashboard-profile-title">
        <div class="fortun-dashboard-section-heading">
            <div>
                <p class="fortun-dashboard-section-heading__eyebrow">{{ __('account.dashboard.profile_label') }}</p>
                <h2 id="fortun-dashboard-profile-title">{{ $aiCopy['profile_title'] }}</h2>
                <p>{{ $aiCopy['profile_text'] }}</p>
            </div>
            <a href="{{ route('account.profile.edit') }}" class="miro-button miro-button--dark">{{ __('account.profile.edit') }}</a>
        </div>

        <div class="fortun-dashboard-profile__card">
            <div class="fortun-dashboard-profile__identity">
                <div class="fortun-dashboard-profile__avatar">
                    @if($accountUser->avatar_path)
                        <img src="{{ Storage::url($accountUser->avatar_path) }}" alt="{{ $accountUser->full_name ?: __('account.profile.contact_info') }}">
                    @else
                        <span>{{ mb_strtoupper(mb_substr($accountUser->full_name ?: $accountUser->telegram_username ?: '?', 0, 1)) }}</span>
                    @endif
                </div>
                <div class="min-w-0">
                    <h3>{{ $accountUser->full_name ?: __('account.not_specified') }}</h3>
                    @if($accountUser->telegram_username)
                        <p>{{ '@' . $accountUser->telegram_username }}</p>
                    @else
                        <p>{{ __('account.profile.telegram_empty') }}</p>
                    @endif
                </div>
            </div>

            <div class="fortun-dashboard-profile__status">
                <span class="fortun-profile-badge">
                    <span class="fortun-profile-badge__dot" aria-hidden="true"></span>
                    {{ $aiCopy['profile_public'] }}
                </span>
                @if(empty($accountUser->description) || empty($accountUser->expectation))
                    <div class="fortun-dashboard-profile__warning">
                        <strong>{{ __('account.dashboard.complete_title') }}</strong>
                        <span>{{ __('account.dashboard.complete_text') }}</span>
                    </div>
                @else
                    <span class="fortun-dashboard-profile__ready">{{ __('account.dashboard.profile_ready_title') }}</span>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection
