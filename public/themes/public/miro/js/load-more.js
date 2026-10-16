/*
 * Кнопка «Смотреть ещё» (новости, фотоальбомы).
 *
 * Без скриптов кнопка — обычная ссылка на следующую страницу списка (?page=2). Со скриптом клик перехватывается,
 * следующая порция запрашивается с ?partial=1 (сервер отвечает JSON: готовая разметка и адрес следующей порции, если она
 * есть) и добавляется в конец списка. Страница не перезагружается и не прыгает. Когда больше нечего показывать, кнопка
 * исчезает. Если запрос не удался, браузер просто переходит по ссылке на следующую страницу.
 *
 * Разметка: <div data-load-more data-target="СЕЛЕКТОР СПИСКА"><a href="…?page=2">Смотреть ещё</a></div>.
 */
(function () {
    'use strict';

    var wrap = document.querySelector('[data-load-more]');

    if (!wrap || !window.fetch || !window.URL) return;

    var list = document.querySelector(wrap.getAttribute('data-target') || '');
    var link = wrap.querySelector('a');

    if (!list || !link) return;

    function done() {
        link.classList.remove('is-loading');
        link.removeAttribute('aria-busy');
    }

    function load() {
        if (link.classList.contains('is-loading')) return;

        link.classList.add('is-loading');
        link.setAttribute('aria-busy', 'true');

        var url = new URL(link.href, window.location.href);
        url.searchParams.set('partial', '1');

        fetch(url.toString(), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function (response) {
                if (!response.ok) throw new Error('HTTP ' + response.status);

                return response.json();
            })
            .then(function (data) {
                var box = document.createElement('template');
                box.innerHTML = data.html || '';

                var added = Array.prototype.slice.call(box.content.children);
                added.forEach(function (item) { item.classList.add('is-new'); });
                list.appendChild(box.content);

                if (data.next) {
                    link.href = data.next;
                    done();
                } else {
                    // Больше ничего нет: фокус не должен пропасть вместе с кнопкой.
                    var target = added.length ? added[0].querySelector('a') : null;
                    wrap.remove();
                    if (target) target.focus({ preventScroll: true });
                }
            })
            .catch(function () {
                done();
                window.location.href = link.href;
            });
    }

    link.addEventListener('click', function (event) {
        // Клик с Ctrl, Shift, Cmd или средней кнопкой оставляем браузеру: открыть следующую страницу в новой вкладке.
        if (event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;

        event.preventDefault();
        load();
    });
}());
