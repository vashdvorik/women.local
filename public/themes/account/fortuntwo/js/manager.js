// AI-менеджер кабинета: чат на странице «AI-менеджер» и в плавающем окне. Сообщения уходят на сервер JSON-запросом,
// ответ приходит простым текстом и выводится через textContent (разметки в нём быть не может).
(function () {
    'use strict';

    var tokenMeta = document.querySelector('meta[name="csrf-token"]');
    var csrf = tokenMeta ? tokenMeta.getAttribute('content') : '';

    function initChat(root) {
        var log = root.querySelector('[data-mgr-log]');
        var chips = root.querySelector('[data-mgr-chips]');
        var form = root.querySelector('[data-mgr-form]');
        var input = root.querySelector('[data-mgr-input]');
        var button = root.querySelector('[data-mgr-send]');
        var i18n = {};
        try { i18n = JSON.parse(root.getAttribute('data-i18n') || '{}'); } catch (e) {}
        var loaded = false;
        var busy = false;
        var typing = null;

        function bubble(role, text) {
            var item = document.createElement('div');
            item.className = 'mgr-msg mgr-msg--' + role;
            var body = document.createElement('p');
            body.textContent = text;
            item.appendChild(body);
            log.appendChild(item);
            log.scrollTop = log.scrollHeight;
            return item;
        }

        function showTyping(on) {
            if (on && !typing) { typing = bubble('assistant', i18n.typing || '…'); typing.classList.add('mgr-msg--typing'); }
            if (!on && typing) { typing.remove(); typing = null; }
        }

        function load() {
            if (loaded) return;
            loaded = true;
            fetch(root.getAttribute('data-history-url'), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                .then(function (response) { return response.ok ? response.json() : { messages: [] }; })
                .then(function (data) {
                    var messages = data.messages || [];
                    bubble('assistant', i18n.hello || '');
                    messages.forEach(function (m) { bubble(m.role === 'user' ? 'user' : 'assistant', m.content); });
                    if (messages.length) chips.hidden = true;
                })
                .catch(function () { bubble('assistant', i18n.hello || ''); });
        }

        function send(text) {
            text = (text || '').trim();
            if (!text || busy) return;
            busy = true;
            button.disabled = true;
            chips.hidden = true;
            bubble('user', text);
            input.value = '';
            input.style.height = '';
            showTyping(true);

            fetch(root.getAttribute('data-send-url'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                body: JSON.stringify({ message: text })
            }).then(function (response) {
                return response.json().catch(function () { return {}; }).then(function (data) { return { ok: response.ok, data: data }; });
            }).then(function (result) {
                showTyping(false);
                bubble('assistant', result.ok && result.data.reply ? result.data.reply : (result.data.message || i18n.error || ''));
            }).catch(function () {
                showTyping(false);
                bubble('assistant', i18n.error || '');
            }).then(function () {
                busy = false;
                button.disabled = false;
                input.focus();
            });
        }

        form.addEventListener('submit', function (event) { event.preventDefault(); send(input.value); });
        input.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); send(input.value); }
        });
        input.addEventListener('input', function () {
            input.style.height = 'auto';
            input.style.height = Math.min(input.scrollHeight, 120) + 'px';
        });
        root.querySelectorAll('[data-mgr-suggest]').forEach(function (chip) {
            chip.addEventListener('click', function () { send(chip.textContent); });
        });

        if (root.hasAttribute('data-autoload')) load();

        return { load: load, focus: function () { input.focus(); } };
    }

    document.querySelectorAll('[data-manager-chat]').forEach(function (root) {
        var chat = initChat(root);
        var panel = root.closest('[data-mgr-panel]');
        if (!panel) return;

        var fab = document.querySelector('[data-mgr-open]');
        var close = panel.querySelector('[data-mgr-close]');

        function open() { panel.hidden = false; if (fab) fab.hidden = true; chat.load(); chat.focus(); }
        function shut() { panel.hidden = true; if (fab) fab.hidden = false; }

        if (fab) fab.addEventListener('click', open);
        close.addEventListener('click', shut);
        document.addEventListener('keydown', function (event) { if (event.key === 'Escape' && !panel.hidden) shut(); });
    });
})();
