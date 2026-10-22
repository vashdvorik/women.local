@extends('themes.account.fortuntwo.layout')
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
            'placeholder' => 'Например: “Найдите эксперта по маркетингу”',
            'send' => 'Спросить',
            'generic_response' => 'Подскажу, с чего начать. Выберите нужный раздел платформы:',
            'actions' => [
                ['key' => 'profile', 'label' => 'Заполнить профиль'],
                ['key' => 'partners', 'label' => 'Найти партнёров'],
                ['key' => 'expert', 'label' => 'Найти эксперта'],
                ['key' => 'opportunities', 'label' => 'Подобрать возможности'],
                ['key' => 'matching', 'label' => 'Как работает подбор контактов?'],
            ],
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
            'placeholder' => 'For example: “Find a marketing expert”',
            'send' => 'Ask',
            'generic_response' => 'I can point you in the right direction. Choose a platform section:',
            'actions' => [
                ['key' => 'profile', 'label' => 'Complete your profile'],
                ['key' => 'partners', 'label' => 'Find partners'],
                ['key' => 'expert', 'label' => 'Find an expert'],
                ['key' => 'opportunities', 'label' => 'Find opportunities'],
                ['key' => 'matching', 'label' => 'How does contact matching work?'],
            ],
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
            'placeholder' => 'De exemplu: „Găsiți un expert în marketing”',
            'send' => 'Întreabă',
            'generic_response' => 'Vă pot indica direcția potrivită. Alegeți o secțiune a platformei:',
            'actions' => [
                ['key' => 'profile', 'label' => 'Completați profilul'],
                ['key' => 'partners', 'label' => 'Găsiți parteneri'],
                ['key' => 'expert', 'label' => 'Găsiți un expert'],
                ['key' => 'opportunities', 'label' => 'Găsiți oportunități'],
                ['key' => 'matching', 'label' => 'Cum funcționează potrivirea contactelor?'],
            ],
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
                    <p id="fortun-ai-title" class="fortun-ai-chat-header__role">{{ $copy['role'] }}</p>
                </div>
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
        const descriptionLabel = @json(__('account.profile.description'));
        const expectationLabel = @json(__('account.profile.expectation'));
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
                // Show the actual proposed text before asking the user to confirm — a bare
                // "Save" button with nothing to check made the assistant's own "review it"
                // message meaningless.
                const preview = document.createElement('div');
                preview.className = 'fortun-ai-proposal';
                [['description', descriptionLabel], ['expectation', expectationLabel]].forEach(([field, label]) => {
                    if (!result.profile_proposal[field]) return;
                    const row = document.createElement('p');
                    row.className = 'fortun-ai-proposal__row';
                    const term = document.createElement('strong');
                    term.textContent = label + ':';
                    row.append(term, document.createTextNode(' ' + result.profile_proposal[field]));
                    preview.append(row);
                });
                bubble.append(preview);

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

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) {
                event.preventDefault();
                form.requestSubmit();
            }
        });

        root.querySelectorAll('[data-ai-query]').forEach((button) => {
            button.addEventListener('click', () => {
                input.value = button.dataset.aiQuery || '';
                form.requestSubmit();
            });
        });

        const unavailableText = @json(__('account.assistant.unavailable'));
        const HISTORY_LIMIT = 8; // matches the server's own validation cap and the window it actually reads
        const pushHistory = (role, content) => {
            history.push({role, content});
            if (history.length > HISTORY_LIMIT) history.splice(0, history.length - HISTORY_LIMIT);
        };

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const message = input.value.trim(); if (!message) return;
            addMessage('user', message); pushHistory('user', message); input.value = ''; setLoading(true);
            try {
                const response = await fetch(messageUrl, { method: 'POST', headers: {'Content-Type': 'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': csrf}, body: JSON.stringify({message, history}) });
                const result = await response.json();
                // Only ever show model-generated text from a successful response. On any
                // failure (validation error, provider outage, ...) the server's error
                // shapes vary and are not meant for end users — always fall back to our
                // own localized message instead of surfacing raw backend text.
                const text = (response.ok && typeof result.reply === 'string' && result.reply !== '') ? result.reply : unavailableText;
                addMessage('assistant', text, response.ok ? result : null); pushHistory('assistant', text);
            } catch (_) { addMessage('assistant', unavailableText); }
            finally { setLoading(false); input.focus(); }
        });
    })();
</script>
@endpush
