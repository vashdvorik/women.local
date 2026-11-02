{{--
    Чат с AI-менеджером: оформлен так же, как чат AI-помощника (классы fortun-ai-*). Один и тот же на странице
    «AI-менеджер» и в плавающем окне (manager-widget). Поведение — public/themes/account/fortuntwo/js/manager.js (находит
    блоки с data-manager-chat). $autoload — загрузить историю сразу (на странице), иначе окно загрузит её при первом открытии.
    $withHeader — показать шапку с названием (на странице; в окне своя шапка).
--}}
@php
    $chatId = ($autoload ?? false) ? 'page' : 'widget';
    $i18n = [
        'hello' => __('account.manager.hello'),
        'typing' => __('account.manager.typing'),
        'error' => __('account.manager.error'),
        'author' => __('account.manager.title'),
    ];
@endphp
<div class="fortun-ai-chat-window mgr-chat" data-manager-chat data-history-url="{{ route('account.manager.history') }}" data-send-url="{{ route('account.manager.message') }}" @if($autoload ?? false) data-autoload @endif
     data-i18n='@json($i18n)'>
    @if($withHeader ?? false)
        <header class="fortun-ai-chat-header">
            <div class="fortun-ai-chat-header__identity">
                <span class="fortun-ai-assistant__mark" aria-hidden="true">AI</span>
                <p class="fortun-ai-chat-header__role">{{ __('account.manager.title') }}</p>
            </div>
        </header>
    @endif

    <div class="fortun-ai-chat-body" data-mgr-log role="log" aria-live="polite">
        <div class="fortun-ai-thread" data-mgr-thread></div>
    </div>

    {{-- Быстрые вопросы: скрипт переносит их под первое приветствие, как в чате AI-помощника --}}
    <div hidden>
        <div class="fortun-ai-quick-replies" data-mgr-chips>
            @foreach(['s1', 's2', 's3', 's4'] as $key)
                <button type="button" class="fortun-ai-quick-reply" data-mgr-suggest>{{ __('account.manager.'.$key) }}</button>
            @endforeach
        </div>
    </div>

    <form class="fortun-ai-composer" data-mgr-form>
        <span class="fortun-ai-composer__avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($accountUser->full_name ?: 'U', 0, 1)) }}</span>
        <label class="sr-only" for="mgr-input-{{ $chatId }}">{{ __('account.manager.placeholder') }}</label>
        <input id="mgr-input-{{ $chatId }}" type="text" data-mgr-input maxlength="1000" autocomplete="off" placeholder="{{ __('account.manager.placeholder') }}">
        <button type="submit" class="fortun-ai-composer__send" data-mgr-send aria-label="{{ __('account.manager.send') }}">
            <span>{{ __('account.manager.send') }}</span>
            <span aria-hidden="true">↑</span>
        </button>
    </form>
    <p class="mgr-note">{{ __('account.manager.note') }}</p>
</div>
