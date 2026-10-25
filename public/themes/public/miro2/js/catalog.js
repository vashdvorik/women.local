(function () {
    'use strict';

    // Каталог с перелистыванием (страница /media/publications/catalog/…). Страницы — готовые картинки WebP: разбирать PDF
    // на устройстве посетителя не нужно. Чтобы не нагружать устройство, в браузер подгружаются только текущая страница и
    // несколько соседних (по мере листания добавляются следующие), а сама анимация перелистывания работает только в момент
    // листания: пока страницу не трогают, никакие кадры не считаются.
    var root = document.querySelector('[data-flip]');
    if (!root || !window.St || !window.St.PageFlip) return;

    var pages;
    try { pages = JSON.parse(root.dataset.pages || '[]'); } catch (e) { pages = []; }
    if (!pages.length) return;

    var ratio = parseFloat(root.dataset.ratio) || 0.7071;
    var AHEAD = 3; // сколько страниц вперёд и назад держать загруженными
    var book = root.querySelector('[data-flip-book]');
    var bar = root.querySelector('[data-flip-bar]');
    var count = root.querySelector('[data-flip-count]');

    // Страницы-заготовки: картинка получает адрес только когда страница оказывается рядом
    var nodes = pages.map(function (url, index) {
        var page = document.createElement('div');
        page.className = 'miro-flip__page';
        // Обложка и последняя страница твёрдые, как у настоящей книги
        if (index === 0 || index === pages.length - 1) page.dataset.density = 'hard';

        var img = document.createElement('img');
        img.alt = '';
        img.draggable = false;
        img.decoding = 'async';
        img.dataset.src = url;
        page.appendChild(img);

        return page;
    });

    var loaded = {};
    function load(index) {
        if (index < 0 || index >= pages.length || loaded[index]) return;
        var img = nodes[index].querySelector('img');
        if (img && img.dataset.src) {
            img.src = img.dataset.src;
            loaded[index] = true;
        }
    }
    function loadAround(index) {
        for (var i = index - AHEAD; i <= index + AHEAD + 1; i++) load(i);
    }

    var flip = new window.St.PageFlip(book, {
        width: 400,
        height: Math.round(400 / ratio),
        size: 'stretch',
        minWidth: 220,
        maxWidth: 640,
        minHeight: Math.round(220 / ratio),
        maxHeight: Math.round(640 / ratio),
        maxShadowOpacity: 0.4,
        showCover: true,
        mobileScrollSupport: false,
        usePortrait: true,
        flippingTime: 650,
        drawShadow: true,
        startPage: 0,
    });

    loadAround(0);
    flip.loadFromHTML(nodes);

    function update(index) {
        loadAround(index);
        var total = pages.length;
        var landscape = flip.getOrientation && flip.getOrientation() === 'landscape';
        var text;

        if (landscape && index > 0 && index < total - 1) {
            // Разворот: слева чётная по счёту страница, справа следующая
            var left = index % 2 === 1 ? index : index - 1;
            text = (left + 1) + '–' + Math.min(left + 2, total) + ' / ' + total;
        } else {
            text = (index + 1) + ' / ' + total;
        }

        if (count) count.textContent = text;
    }

    flip.on('flip', function (event) { update(event.data); });
    flip.on('changeOrientation', function () { update(flip.getCurrentPageIndex()); });
    update(0);

    if (bar) bar.hidden = false;

    var prev = root.querySelector('[data-flip-prev]');
    var next = root.querySelector('[data-flip-next]');
    var full = root.querySelector('[data-flip-full]');

    if (prev) prev.addEventListener('click', function () { flip.flipPrev(); });
    if (next) next.addEventListener('click', function () { flip.flipNext(); });

    if (full) {
        if (!root.requestFullscreen) {
            full.hidden = true;
        } else {
            full.addEventListener('click', function () {
                if (document.fullscreenElement) document.exitFullscreen();
                else root.requestFullscreen().catch(function () {});
            });
        }
    }

    document.addEventListener('keydown', function (event) {
        var tag = (event.target && event.target.tagName) || '';
        if (/^(INPUT|TEXTAREA|SELECT)$/.test(tag) || event.metaKey || event.ctrlKey || event.altKey) return;
        if (event.key === 'ArrowRight') flip.flipNext();
        else if (event.key === 'ArrowLeft') flip.flipPrev();
    });

    // Вернулись из полноэкранного режима или повернули телефон — книга сама подстроится под новый размер контейнера
    document.addEventListener('fullscreenchange', function () { window.dispatchEvent(new Event('resize')); });
})();
