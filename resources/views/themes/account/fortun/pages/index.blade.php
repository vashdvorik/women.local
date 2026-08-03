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
    <section class="fortun-ai-assistant" aria-labelledby="fortun-ai-title">
        <div class="fortun-ai-chat-window">
            <header class="fortun-ai-chat-header">
                <div class="fortun-ai-chat-header__identity">
                    <span class="fortun-ai-assistant__mark" aria-hidden="true">AI</span>
                    <div>
                        <div class="fortun-ai-chat-header__name-row">
                            <h1 id="fortun-ai-title">{{ $copy['label'] }}</h1>
                            <span class="fortun-ai-chat-header__status"><i aria-hidden="true"></i>{{ $copy['status'] }}</span>
                        </div>
                        <p>{{ $copy['role'] }}</p>
                    </div>
                </div>
                <span class="fortun-ai-chat-header__spark" aria-hidden="true">✦</span>
            </header>

            <div class="fortun-ai-chat-body" aria-label="{{ $copy['label'] }}">
                <div class="fortun-ai-thread">
                    <div class="fortun-ai-message fortun-ai-message--assistant">
                        <span class="fortun-ai-message__avatar" aria-hidden="true">AI</span>
                        <div class="fortun-ai-message__bubble">
                            <span class="fortun-ai-message__author">{{ $copy['label'] }}</span>
                            <p>{{ $copy['welcome'] }}</p>
                            <div class="fortun-ai-quick-replies" aria-label="{{ $copy['label'] }}">
                                @foreach($copy['actions'] as $action)
                                    <button type="button" class="fortun-ai-quick-reply" data-ai-query="{{ $action['label'] }}">{{ $action['label'] }}</button>
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

    </section>
</div>
@endsection

@push('head')
<style>
    .fortun-ai-composer__send.is-loading { cursor: wait; opacity: .78; }
    .fortun-ai-composer__send.is-loading [data-ai-send-spinner] { display: inline-block; }
    [data-ai-send-spinner] { display: none; width: .9rem; height: .9rem; border: 2px solid currentColor; border-right-color: transparent; border-radius: 999px; animation: fortun-ai-spin .7s linear infinite; }
    @keyframes fortun-ai-spin { to { transform: rotate(360deg); } }
</style>
@endpush

@push('scripts')
<script>
    (() => {
        const root = document.querySelector('.fortun-ai-assistant');
        if (!root) return;

        const thread = root.querySelector('.fortun-ai-thread');
        const chatBody = root.querySelector('.fortun-ai-chat-body');
        const input = root.querySelector('[data-ai-input]');
        const form = root.querySelector('[data-ai-form]');
        const send = form.querySelector('button[type="submit"]');
        const history = [];
        const messageUrl = @json(route('account.assistant.message'));
        const profileUrl = @json(route('account.assistant.profile-update'));
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        const assistantLabel = @json($copy['label']);
        const openLabel = @json(__('account.assistant.open'));
        const saveLabel = @json(__('account.assistant.save_changes'));
        const sendLabel = @json($copy['send']);
        const generatingLabel = @json(['ru' => 'Думаю…', 'en' => 'Thinking…', 'ro' => 'Mă gândesc…'][app()->getLocale()] ?? 'Thinking…');
        const sendText = send.querySelector('span:first-child');
        const sendIcon = send.querySelector('span:last-child');

        const setLoading = (loading) => {
            send.disabled = loading;
            send.classList.toggle('is-loading', loading);
            sendText.textContent = loading ? generatingLabel : sendLabel;
            sendIcon.innerHTML = loading ? '<i data-ai-send-spinner aria-hidden="true"></i>' : '↑';
        };

        const addMessage = (role, content, result = null) => {
            const message = document.createElement('div');
            message.className = `fortun-ai-message fortun-ai-message--${role}`;
            if (role === 'assistant') {
                const avatar = document.createElement('span');
                avatar.className = 'fortun-ai-message__avatar';
                avatar.setAttribute('aria-hidden', 'true');
                avatar.textContent = 'AI';
                message.append(avatar);
            }
            const bubble = document.createElement('div');
            bubble.className = 'fortun-ai-message__bubble';
            const author = document.createElement('span');
            author.className = 'fortun-ai-message__author';
            author.textContent = role === 'assistant' ? assistantLabel : '';
            const text = document.createElement('p');
            text.textContent = content;
            bubble.append(author, text);

            if (result?.recommendation) {
                const action = document.createElement('a');
                action.href = result.recommendation.url;
                action.className = 'fortun-ai-response__link';
                action.textContent = `${openLabel}: ${result.recommendation.label}`;
                bubble.append(action);
            }
            if (result?.profile_proposal) {
                const save = document.createElement('button');
                save.type = 'button'; save.className = 'fortun-ai-response__link'; save.textContent = saveLabel;
                save.addEventListener('click', async () => {
                    save.disabled = true;
                    const response = await fetch(profileUrl, { method: 'POST', headers: {'Content-Type': 'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': csrf}, body: JSON.stringify(result.profile_proposal) });
                    const data = await response.json();
                    addMessage('assistant', data.message || @json(__('account.assistant.profile_saved')));
                    save.remove();
                });
                bubble.append(save);
            }
            message.append(bubble);
            thread.append(message);
            chatBody.scrollTo({ top: chatBody.scrollHeight, behavior: 'smooth' });
        };

        root.querySelectorAll('[data-ai-query]').forEach((button) => {
            button.addEventListener('click', () => {
                input.value = button.dataset.aiQuery || '';
                form.requestSubmit();
            });
        });

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const message = input.value.trim(); if (!message) return;
            addMessage('user', message); history.push({role: 'user', content: message}); input.value = ''; setLoading(true);
            try {
                const response = await fetch(messageUrl, { method: 'POST', headers: {'Content-Type': 'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': csrf}, body: JSON.stringify({message, history}) });
                const result = await response.json();
                const text = result.reply || result.message || @json(__('account.assistant.unavailable'));
                addMessage('assistant', text, result); history.push({role: 'assistant', content: text});
            } catch (_) { addMessage('assistant', @json(__('account.assistant.unavailable'))); }
            finally { setLoading(false); input.focus(); }
        });
    })();
</script>
@endpush
