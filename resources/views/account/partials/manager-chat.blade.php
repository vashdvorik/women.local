{{--
    Чат с AI-менеджером: один и тот же на странице «AI-менеджер» и в плавающем окне (manager-widget). Поведение —
    public/themes/account/fortuntwo/js/manager.js (находит блоки с data-manager-chat). $autoload — загрузить историю сразу
    (на странице), иначе окно загрузит её при первом открытии.
--}}
<div class="mgr-chat" data-manager-chat data-history-url="{{ route('account.manager.history') }}" data-send-url="{{ route('account.manager.message') }}" @if($autoload ?? false) data-autoload @endif
     data-i18n='@json(['hello' => __('account.manager.hello'), 'typing' => __('account.manager.typing'), 'error' => __('account.manager.error')])'>
    <div class="mgr-log" data-mgr-log role="log" aria-live="polite"></div>

    <div class="mgr-chips" data-mgr-chips>
        @foreach(['s1', 's2', 's3', 's4'] as $key)
            <button type="button" class="mgr-chip" data-mgr-suggest>{{ __('account.manager.'.$key) }}</button>
        @endforeach
    </div>

    <form class="mgr-form" data-mgr-form>
        <label class="sr-only" for="mgr-input-{{ $autoload ?? false ? 'page' : 'widget' }}">{{ __('account.manager.placeholder') }}</label>
        <textarea id="mgr-input-{{ $autoload ?? false ? 'page' : 'widget' }}" class="mgr-input" data-mgr-input rows="1" maxlength="1000" placeholder="{{ __('account.manager.placeholder') }}"></textarea>
        <button type="submit" class="mgr-send" data-mgr-send aria-label="{{ __('account.manager.send') }}">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
        </button>
    </form>
    <p class="mgr-note">{{ __('account.manager.note') }}</p>
</div>
