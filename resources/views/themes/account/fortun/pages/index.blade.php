@extends('themes.account.fortun.layout')
@section('title', __('account.dashboard.title'))

@php
    $locale = app()->getLocale();
    $copy = [
        'ru' => [
            'label' => 'Fortun AI',
            'status' => 'Онлайн',
            'role' => 'AI-помощник платформы',
            'title' => 'Найдите партнёров и возможности с помощью ИИ',
            'subtitle' => 'Помогу заполнить профиль, найти подходящих партнёров и экспертов, а также подобрать возможности для вашего бизнеса',
            'welcome' => 'Здравствуйте! Я помогу найти партнёров, экспертов и возможности для вашего бизнеса.',
            'user_message' => 'Хочу найти партнёров',
            'assistant_response' => 'Отлично. Я могу подобрать контакты по вашей сфере и задачам. С чего начнём?',
            'popular' => 'Популярные запросы:',
            'placeholder' => 'Например: “Найдите эксперта по маркетингу”',
            'send' => 'Спросить',
            'generic_response' => 'Подскажу, с чего начать. Выберите нужный раздел платформы:',
            'actions' => [
                ['key' => 'profile', 'label' => 'Заполнить профиль', 'response' => 'Заполните описание и ожидания — так ИИ сможет точнее подобрать для вас людей и возможности.', 'links' => [['label' => 'Открыть профиль', 'url' => route('account.profile.edit')]]],
                ['key' => 'partners', 'label' => 'Найти партнёров', 'response' => 'Откройте подбор участников с похожими интересами, запросами и опытом.', 'links' => [['label' => 'Найти партнёров', 'url' => route('account.matches')], ['label' => 'Все участники', 'url' => route('account.people')]]],
                ['key' => 'expert', 'label' => 'Найти эксперта', 'response' => 'Опишите задачу в поиске — например, маркетинг, продажи или выход на новый рынок.', 'links' => [['label' => 'Открыть поиск', 'url' => route('account.search')]]],
                ['key' => 'opportunities', 'label' => 'Подобрать возможности', 'response' => 'Посмотрите актуальные возможности, встречи и запросы, которые можно добавить в свой маршрут развития.', 'links' => [['label' => 'Открыть возможности', 'url' => route('account.opportunities.index')]]],
            ],
            'queries' => ['Как заполнить профиль?', 'Кого можно найти на платформе?', 'Где посмотреть возможности?', 'Как работает подбор контактов?'],
            'profile_title' => 'Ваша визитка',
            'profile_text' => 'Так вас видят участницы платформы. Чем точнее описание, тем полезнее будут будущие рекомендации.',
            'profile_public' => 'Виден участницам',
        ],
        'en' => [
            'label' => 'Fortun AI',
            'status' => 'Online',
            'role' => 'AI platform assistant',
            'title' => 'Find partners and opportunities with AI',
            'subtitle' => 'I can help you complete your profile, find the right partners and experts, and discover opportunities for your business',
            'welcome' => 'Hello! I can help you find partners, experts and opportunities for your business.',
            'user_message' => 'I want to find partners',
            'assistant_response' => 'Great. I can match contacts to your field and goals. Where shall we start?',
            'popular' => 'Popular requests:',
            'placeholder' => 'For example: “Find a marketing expert”',
            'send' => 'Ask',
            'generic_response' => 'I can point you in the right direction. Choose a platform section:',
            'actions' => [
                ['key' => 'profile', 'label' => 'Complete your profile', 'response' => 'Add your description and expectations so AI can find more relevant people and opportunities for you.', 'links' => [['label' => 'Open profile', 'url' => route('account.profile.edit')]]],
                ['key' => 'partners', 'label' => 'Find partners', 'response' => 'Explore participants with similar interests, needs and experience.', 'links' => [['label' => 'Find partners', 'url' => route('account.matches')], ['label' => 'All participants', 'url' => route('account.people')]]],
                ['key' => 'expert', 'label' => 'Find an expert', 'response' => 'Describe your need in Search — for example, marketing, sales or entering a new market.', 'links' => [['label' => 'Open search', 'url' => route('account.search')]]],
                ['key' => 'opportunities', 'label' => 'Find opportunities', 'response' => 'Explore current opportunities, events and requests that can support your next step.', 'links' => [['label' => 'Open opportunities', 'url' => route('account.opportunities.index')]]],
            ],
            'queries' => ['How do I complete my profile?', 'Who can I find on the platform?', 'Where can I see opportunities?', 'How does contact matching work?'],
            'profile_title' => 'Your business card',
            'profile_text' => 'This is how platform participants see you. The clearer your description, the more useful future recommendations will be.',
            'profile_public' => 'Visible to participants',
        ],
        'ro' => [
            'label' => 'Fortun AI',
            'status' => 'Online',
            'role' => 'Asistentul AI al platformei',
            'title' => 'Găsiți parteneri și oportunități cu ajutorul AI',
            'subtitle' => 'Vă ajut să completați profilul, să găsiți partenerii și experții potriviți și să descoperiți oportunități pentru afacerea dvs.',
            'welcome' => 'Bună ziua! Vă ajut să găsiți parteneri, experți și oportunități pentru afacerea dvs.',
            'user_message' => 'Vreau să găsesc parteneri',
            'assistant_response' => 'Perfect. Pot selecta contacte după domeniul și obiectivele dvs. Cu ce începem?',
            'popular' => 'Solicitări populare:',
            'placeholder' => 'De exemplu: „Găsiți un expert în marketing”',
            'send' => 'Întreabă',
            'generic_response' => 'Vă pot indica direcția potrivită. Alegeți o secțiune a platformei:',
            'actions' => [
                ['key' => 'profile', 'label' => 'Completați profilul', 'response' => 'Adăugați descrierea și așteptările pentru ca AI să vă poată recomanda persoane și oportunități relevante.', 'links' => [['label' => 'Deschideți profilul', 'url' => route('account.profile.edit')]]],
                ['key' => 'partners', 'label' => 'Găsiți parteneri', 'response' => 'Descoperiți participante cu interese, nevoi și experiență similare.', 'links' => [['label' => 'Găsiți parteneri', 'url' => route('account.matches')], ['label' => 'Toate participantele', 'url' => route('account.people')]]],
                ['key' => 'expert', 'label' => 'Găsiți un expert', 'response' => 'Descrieți nevoia în secțiunea Căutare — de exemplu marketing, vânzări sau accesarea unei piețe noi.', 'links' => [['label' => 'Deschideți căutarea', 'url' => route('account.search')]]],
                ['key' => 'opportunities', 'label' => 'Găsiți oportunități', 'response' => 'Descoperiți oportunități, evenimente și solicitări care vă pot susține următorul pas.', 'links' => [['label' => 'Deschideți oportunitățile', 'url' => route('account.opportunities.index')]]],
            ],
            'queries' => ['Cum completez profilul?', 'Pe cine pot găsi pe platformă?', 'Unde văd oportunitățile?', 'Cum funcționează potrivirea contactelor?'],
            'profile_title' => 'Cartea dvs. de vizită',
            'profile_text' => 'Așa vă văd participantele platformei. Cu cât descrierea este mai clară, cu atât recomandările viitoare vor fi mai utile.',
            'profile_public' => 'Vizibil pentru participante',
        ],
    ][$locale] ?? [];
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

        <div class="fortun-ai-chat-window">
            <header class="fortun-ai-chat-header">
                <div class="fortun-ai-chat-header__identity">
                    <span class="fortun-ai-assistant__mark" aria-hidden="true">AI</span>
                    <div>
                        <div class="fortun-ai-chat-header__name-row">
                            <strong>{{ $copy['label'] }}</strong>
                            <span class="fortun-ai-chat-header__status"><i aria-hidden="true"></i>{{ $copy['status'] }}</span>
                        </div>
                        <p>{{ $copy['role'] }}</p>
                    </div>
                </div>
                <span class="fortun-ai-chat-header__spark" aria-hidden="true">✦</span>
            </header>

            <div class="fortun-ai-chat-body" aria-label="{{ $copy['label'] }}">
                <div class="fortun-ai-chat-intro">
                    <p class="fortun-ai-chat-intro__eyebrow">{{ $copy['label'] }}</p>
                    <h2 id="fortun-ai-title">{{ $copy['title'] }}</h2>
                    <p>{{ $copy['subtitle'] }}</p>
                </div>

                <div class="fortun-ai-thread">
                    <div class="fortun-ai-message fortun-ai-message--assistant">
                        <span class="fortun-ai-message__avatar" aria-hidden="true">AI</span>
                        <div class="fortun-ai-message__bubble">
                            <span class="fortun-ai-message__author">{{ $copy['label'] }}</span>
                            <p>{{ $copy['welcome'] }}</p>
                        </div>
                    </div>

                    <div class="fortun-ai-message fortun-ai-message--user">
                        <div class="fortun-ai-message__bubble">
                            <span class="fortun-ai-message__author">{{ __('account.profile.title') }}</span>
                            <p>{{ $copy['user_message'] }}</p>
                        </div>
                    </div>

                    <div class="fortun-ai-message fortun-ai-message--assistant">
                        <span class="fortun-ai-message__avatar" aria-hidden="true">AI</span>
                        <div class="fortun-ai-message__bubble">
                            <span class="fortun-ai-message__author">{{ $copy['label'] }}</span>
                            <p>{{ $copy['assistant_response'] }}</p>

                            <div class="fortun-ai-quick-replies" aria-label="{{ $copy['label'] }}">
                                @foreach($copy['actions'] as $action)
                                    <button type="button" class="fortun-ai-quick-reply" data-ai-action="{{ $action['key'] }}">{{ $action['label'] }}</button>
                                @endforeach
                            </div>

                            <div class="fortun-ai-popular">
                                <p class="fortun-ai-popular__title">{{ $copy['popular'] }}</p>
                                <div class="fortun-ai-popular__list">
                                    @foreach($copy['queries'] as $query)
                                        <button type="button" class="fortun-ai-query" data-ai-query="{{ $query }}">{{ $query }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="fortun-ai-response fortun-ai-message fortun-ai-message--assistant" data-ai-response hidden aria-live="polite">
                        <span class="fortun-ai-message__avatar" aria-hidden="true">AI</span>
                        <div class="fortun-ai-message__bubble">
                            <span class="fortun-ai-message__author">{{ $copy['label'] }}</span>
                            <p data-ai-response-text></p>
                            <div class="fortun-ai-response__actions" data-ai-response-actions></div>
                        </div>
                    </div>
                </div>
            </div>

            <form class="fortun-ai-composer" data-ai-form>
                <span class="fortun-ai-composer__avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($accountUser->full_name ?: 'U', 0, 1)) }}</span>
                <input type="text" name="question" data-ai-input placeholder="{{ $copy['placeholder'] }}" aria-label="{{ $copy['placeholder'] }}">
                <button type="submit" class="fortun-ai-composer__send" aria-label="{{ $copy['send'] }}">
                    <span>{{ $copy['send'] }}</span>
                    <span aria-hidden="true">↑</span>
                </button>
            </form>
        </div>

        <div class="fortun-ai-response-data" hidden>
            @foreach($copy['actions'] as $action)
                <div data-ai-response-item="{{ $action['key'] }}" data-ai-response-message="{{ $action['response'] }}">
                    @foreach($action['links'] as $link)
                        <a href="{{ $link['url'] }}">{{ $link['label'] }}</a>
                    @endforeach
                </div>
            @endforeach
            <div data-ai-response-item="generic" data-ai-response-message="{{ $copy['generic_response'] }}">
                <a href="{{ route('account.search') }}">{{ __('account.nav.search') }}</a>
                <a href="{{ route('account.matches') }}">{{ __('account.nav.matches') }}</a>
                <a href="{{ route('account.opportunities.index') }}">{{ __('account.nav.opportunities') }}</a>
            </div>
        </div>
    </section>

    <section class="fortun-dashboard-profile" aria-labelledby="fortun-dashboard-profile-title">
        <div class="fortun-dashboard-section-heading">
            <div>
                <p class="fortun-dashboard-section-heading__eyebrow">{{ __('account.dashboard.profile_label') }}</p>
                <h2 id="fortun-dashboard-profile-title">{{ $copy['profile_title'] }}</h2>
                <p>{{ $copy['profile_text'] }}</p>
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
                    {{ $copy['profile_public'] }}
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

@push('scripts')
<script>
    (() => {
        const root = document.querySelector('.fortun-ai-assistant');
        if (!root) return;

        const response = root.querySelector('[data-ai-response]');
        const responseText = root.querySelector('[data-ai-response-text]');
        const responseActions = root.querySelector('[data-ai-response-actions]');
        const input = root.querySelector('[data-ai-input]');
        const data = root.querySelector('.fortun-ai-response-data');

        const showResponse = (key) => {
            const item = data.querySelector(`[data-ai-response-item="${key}"]`);
            if (!item) return;

            responseText.textContent = item.dataset.aiResponseMessage || '';
            responseActions.replaceChildren(...Array.from(item.querySelectorAll('a')).map((link) => {
                const action = document.createElement('a');
                action.href = link.href;
                action.className = 'fortun-ai-response__link';
                action.textContent = link.textContent;
                return action;
            }));
            response.hidden = false;
            response.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        };

        root.querySelectorAll('[data-ai-action]').forEach((button) => {
            button.addEventListener('click', () => showResponse(button.dataset.aiAction));
        });

        root.querySelectorAll('[data-ai-query]').forEach((button) => {
            button.addEventListener('click', () => {
                input.value = button.dataset.aiQuery || '';
                input.focus();
            });
        });

        root.querySelector('[data-ai-form]').addEventListener('submit', (event) => {
            event.preventDefault();
            showResponse('generic');
        });
    })();
</script>
@endpush
