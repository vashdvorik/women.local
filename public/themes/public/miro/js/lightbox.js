/*
 * Просмотр фото по клику на страницах статей (новости, публикации, возможности, альбомы).
 *
 * Фото в статье — ссылка `a.miro-zoom[data-lightbox]` на сам файл: без скриптов она просто открывает картинку,
 * а здесь клик перехватывается и фото открывается поверх страницы. Листать можно стрелками на экране, клавишами
 * ← и → и свайпом; закрыть — крестик, Esc или клик мимо фото. Листаются только фото, видимые на странице: в статье
 * три языковых копии текста, и скрытые копии в просмотр не попадают.
 */
(function () {
    'use strict';

    var LABELS = {
        ru: { dialog: 'Просмотр фото', close: 'Закрыть', prev: 'Предыдущее фото', next: 'Следующее фото', of: 'из' },
        en: { dialog: 'Photo viewer', close: 'Close', prev: 'Previous photo', next: 'Next photo', of: 'of' },
        ro: { dialog: 'Vizualizare foto', close: 'Închide', prev: 'Fotografia anterioară', next: 'Fotografia următoare', of: 'din' }
    };

    var ICONS = {
        close: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>',
        prev: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>',
        next: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg>'
    };

    var overlay = null;
    var image = null;
    var counter = null;
    var buttons = {};
    var items = [];
    var index = 0;
    var opener = null;
    var touchStart = null;

    function text() {
        return LABELS[document.documentElement.lang] || LABELS.ru;
    }

    function button(kind, extraClass) {
        var el = document.createElement('button');
        el.type = 'button';
        el.className = 'miro-lightbox__btn ' + extraClass;
        el.innerHTML = ICONS[kind];
        buttons[kind] = el;

        return el;
    }

    function build() {
        overlay = document.createElement('div');
        overlay.className = 'miro-lightbox';
        overlay.hidden = true;
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');

        var figure = document.createElement('figure');
        figure.className = 'miro-lightbox__figure';
        image = document.createElement('img');
        image.className = 'miro-lightbox__img';
        image.alt = '';
        figure.appendChild(image);

        counter = document.createElement('div');
        counter.className = 'miro-lightbox__counter';
        counter.setAttribute('aria-live', 'polite');

        overlay.appendChild(figure);
        overlay.appendChild(button('close', 'miro-lightbox__close'));
        overlay.appendChild(button('prev', 'miro-lightbox__prev'));
        overlay.appendChild(button('next', 'miro-lightbox__next'));
        overlay.appendChild(counter);
        document.body.appendChild(overlay);

        buttons.close.addEventListener('click', close);
        buttons.prev.addEventListener('click', function () { step(-1); });
        buttons.next.addEventListener('click', function () { step(1); });

        // Клик мимо фото и кнопок закрывает просмотр.
        overlay.addEventListener('click', function (event) {
            if (event.target === overlay || event.target.classList.contains('miro-lightbox__figure')) close();
        });

        overlay.addEventListener('touchstart', function (event) {
            touchStart = event.touches.length === 1 ? { x: event.touches[0].clientX, y: event.touches[0].clientY } : null;
        }, { passive: true });
        overlay.addEventListener('touchend', function (event) {
            if (!touchStart || !event.changedTouches.length) return;
            var dx = event.changedTouches[0].clientX - touchStart.x;
            var dy = event.changedTouches[0].clientY - touchStart.y;
            touchStart = null;
            // Горизонтальный свайп листает; вертикальный прокрутке мешать не должен.
            if (Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy) * 1.5) step(dx < 0 ? 1 : -1);
        }, { passive: true });
    }

    /** Фото страницы в порядке чтения: только те, что сейчас видны (скрытые языковые копии не считаются). */
    function visibleItems(from) {
        var scope = from.closest('.miro-article') || document;

        return Array.prototype.filter.call(scope.querySelectorAll('a.miro-zoom[data-lightbox]'), function (a) {
            return a.getClientRects().length > 0;
        });
    }

    function show(i) {
        index = (i + items.length) % items.length;
        var href = items[index].getAttribute('href');
        var label = text();

        image.classList.add('is-loading');
        image.onload = function () { image.classList.remove('is-loading'); };
        image.onerror = function () { image.classList.remove('is-loading'); };
        image.src = href;

        overlay.setAttribute('aria-label', label.dialog);
        buttons.close.setAttribute('aria-label', label.close);
        buttons.prev.setAttribute('aria-label', label.prev);
        buttons.next.setAttribute('aria-label', label.next);

        var many = items.length > 1;
        buttons.prev.hidden = !many;
        buttons.next.hidden = !many;
        counter.hidden = !many;
        counter.textContent = (index + 1) + ' ' + label.of + ' ' + items.length;

        // Соседние фото подгружаются заранее: листание без паузы.
        if (many) {
            [1, -1].forEach(function (d) {
                var neighbour = items[(index + d + items.length) % items.length];
                new Image().src = neighbour.getAttribute('href');
            });
        }
    }

    function step(d) {
        if (items.length > 1) show(index + d);
    }

    function open(anchor) {
        if (!overlay) build();

        opener = anchor;
        items = visibleItems(anchor);
        index = Math.max(0, items.indexOf(anchor));

        show(index);

        overlay.hidden = false;
        document.body.classList.add('miro-lightbox-open');
        // Включение прозрачности на следующем кадре: иначе переход не сработает.
        requestAnimationFrame(function () { overlay.classList.add('is-open'); });
        buttons.close.focus();
        document.addEventListener('keydown', onKey);
    }

    function close() {
        if (!overlay || overlay.hidden) return;

        overlay.classList.remove('is-open');
        overlay.hidden = true;
        image.removeAttribute('src');
        document.body.classList.remove('miro-lightbox-open');
        document.removeEventListener('keydown', onKey);

        if (opener && typeof opener.focus === 'function') opener.focus();
        opener = null;
    }

    function onKey(event) {
        if (event.key === 'Escape') {
            close();
        } else if (event.key === 'ArrowLeft') {
            step(-1);
        } else if (event.key === 'ArrowRight') {
            step(1);
        } else if (event.key === 'Tab') {
            // Фокус не уходит за окно просмотра: ходит по видимым кнопкам.
            var focusable = [buttons.close, buttons.prev, buttons.next].filter(function (el) { return !el.hidden; });
            var current = focusable.indexOf(document.activeElement);
            var next = event.shiftKey ? current - 1 : current + 1;

            event.preventDefault();
            focusable[(next + focusable.length) % focusable.length].focus();
        }
    }

    document.addEventListener('click', function (event) {
        var anchor = event.target.closest ? event.target.closest('a.miro-zoom[data-lightbox]') : null;

        if (!anchor || event.defaultPrevented) return;
        // Клик с Ctrl, Shift, Cmd или средней кнопкой оставляем браузеру: открыть файл в новой вкладке.
        if (event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;

        event.preventDefault();
        open(anchor);
    });
}());
