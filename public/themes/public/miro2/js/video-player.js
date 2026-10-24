/*
 * Проигрыватель видео на странице «Видео».
 *
 * Карточка видео — ссылка `a[data-video]` на YouTube с адресом встроенного проигрывателя в атрибуте. Без скриптов она
 * просто открывает YouTube в новой вкладке, а здесь клик перехватывается и видео играет поверх страницы в стилизованном
 * окне (как просмотр фото): затемнение, рамка 16:9 со скруглёнными углами, название, закрытие крестиком, Esc или кликом
 * мимо. При закрытии ролик выгружается, звук не остаётся играть в фоне. Ссылка «Смотреть на YouTube» открывает
 * видео на самом YouTube.
 *
 * Esc закрывает окно, пока фокус на странице; если нажать внутрь самого ролика, клавиши достаются ему (так устроен
 * встроенный проигрыватель YouTube), тогда закрыть можно крестиком или кликом мимо.
 */
(function () {
    'use strict';

    var LABELS = {
        ru: { dialog: 'Видеопроигрыватель', close: 'Закрыть', youtube: 'Смотреть на YouTube' },
        en: { dialog: 'Video player', close: 'Close', youtube: 'Watch on YouTube' },
        ro: { dialog: 'Player video', close: 'Închide', youtube: 'Vezi pe YouTube' }
    };

    var CLOSE_ICON = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>';

    var overlay = null;
    var frame = null;
    var heading = null;
    var closeButton = null;
    var youtubeLink = null;
    var opener = null;

    function lang() {
        return LABELS[document.documentElement.lang] ? document.documentElement.lang : 'ru';
    }

    function build() {
        overlay = document.createElement('div');
        overlay.className = 'miro-player';
        overlay.hidden = true;
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');

        var box = document.createElement('div');
        box.className = 'miro-player__box';

        frame = document.createElement('div');
        frame.className = 'miro-player__frame';

        heading = document.createElement('p');
        heading.className = 'miro-player__title';

        youtubeLink = document.createElement('a');
        youtubeLink.className = 'miro-player__link';
        youtubeLink.target = '_blank';
        youtubeLink.rel = 'noopener noreferrer';

        closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.className = 'miro-player__close';
        closeButton.innerHTML = CLOSE_ICON;

        var caption = document.createElement('div');
        caption.className = 'miro-player__caption';
        caption.appendChild(heading);
        caption.appendChild(youtubeLink);

        box.appendChild(frame);
        box.appendChild(caption);
        overlay.appendChild(box);
        overlay.appendChild(closeButton);
        document.body.appendChild(overlay);

        closeButton.addEventListener('click', close);

        // Клик мимо окна закрывает его.
        overlay.addEventListener('click', function (event) {
            if (event.target === overlay || event.target === box) close();
        });
    }

    /** Название видео на текущем языке: из видимой подписи карточки (три языка лежат в разметке рядом). */
    function titleOf(anchor) {
        var spans = anchor.querySelectorAll('span[data-lang]');

        for (var i = 0; i < spans.length; i++) {
            if (spans[i].getClientRects().length > 0) return spans[i].textContent.trim();
        }

        return anchor.textContent.trim();
    }

    function open(anchor) {
        if (!overlay) build();

        var text = LABELS[lang()];
        var title = titleOf(anchor);
        var src = anchor.getAttribute('data-video') + '&autoplay=1&hl=' + lang();

        opener = anchor;

        overlay.setAttribute('aria-label', title || text.dialog);
        closeButton.setAttribute('aria-label', text.close);
        heading.textContent = title;
        youtubeLink.textContent = text.youtube + ' ↗';
        youtubeLink.href = anchor.getAttribute('href');

        // Ролик создаётся при открытии и убирается при закрытии: пока окно закрыто, ничего не грузится и не играет.
        var iframe = document.createElement('iframe');
        iframe.className = 'miro-player__iframe';
        iframe.src = src;
        iframe.title = title || text.dialog;
        iframe.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
        iframe.setAttribute('allowfullscreen', '');
        iframe.setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
        frame.appendChild(iframe);

        overlay.hidden = false;
        document.body.classList.add('miro-player-open');
        // Включение прозрачности на следующем кадре: иначе переход не сработает.
        requestAnimationFrame(function () { overlay.classList.add('is-open'); });
        closeButton.focus();
        document.addEventListener('keydown', onKey);
    }

    function close() {
        if (!overlay || overlay.hidden) return;

        overlay.classList.remove('is-open');
        overlay.hidden = true;
        frame.innerHTML = '';
        document.body.classList.remove('miro-player-open');
        document.removeEventListener('keydown', onKey);

        if (opener && typeof opener.focus === 'function') opener.focus();
        opener = null;
    }

    function onKey(event) {
        if (event.key === 'Escape') {
            close();
        } else if (event.key === 'Tab') {
            // Фокус не уходит за окно: ходит между ссылкой на YouTube и крестиком.
            var focusable = [youtubeLink, closeButton];
            var next = focusable.indexOf(document.activeElement) + (event.shiftKey ? -1 : 1);

            event.preventDefault();
            focusable[(next + focusable.length) % focusable.length].focus();
        }
    }

    document.addEventListener('click', function (event) {
        var anchor = event.target.closest ? event.target.closest('a[data-video]') : null;

        if (!anchor || event.defaultPrevented) return;
        // Клик с Ctrl, Shift, Cmd или средней кнопкой оставляем браузеру: открыть YouTube в новой вкладке.
        if (event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;

        event.preventDefault();
        open(anchor);
    });
}());
