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
    var ZOOMS = [1, 1.5, 2, 2.5]; // шаги приближения; страницы в файле крупнее, чем на экране, поэтому текст остаётся чётким
    var MAX_PAGE_WIDTH = 1700; // самая широкая страница при самом сильном приближении
    var book = root.querySelector('[data-flip-book]');
    var viewport = root.querySelector('[data-flip-viewport]');
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
        maxWidth: MAX_PAGE_WIDTH,
        minHeight: Math.round(220 / ratio),
        maxHeight: Math.round(MAX_PAGE_WIDTH / ratio),
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
    var zoomIn = root.querySelector('[data-flip-zoom-in]');
    var zoomOut = root.querySelector('[data-flip-zoom-out]');

    if (prev) prev.addEventListener('click', function () { flip.flipPrev(); });
    if (next) next.addEventListener('click', function () { flip.flipNext(); });

    function relayout() { window.dispatchEvent(new Event('resize')); }

    // ---- Приближение: книга растягивается в ZOOMS[i] раз, видимая область прокручивается; центр остаётся на месте
    var zoomIndex = 0;

    // Где на развороте лежит содержимое: обложка стоит на правой половине, последняя страница на левой, остальное по центру
    function contentCenter() {
        var index = flip.getCurrentPageIndex();
        var landscape = flip.getOrientation && flip.getOrientation() === 'landscape';
        if (!landscape) return 0.5;
        if (index === 0) return 0.75;
        if (index === pages.length - 1 && pages.length % 2 === 0) return 0.25;
        return 0.5;
    }

    function centerX() {
        if (!viewport || zoomIndex === 0) return;
        viewport.scrollLeft = contentCenter() * viewport.scrollWidth - viewport.clientWidth / 2;
    }

    function setZoom(index) {
        index = Math.max(0, Math.min(ZOOMS.length - 1, index));
        if (index === zoomIndex || !viewport) return;

        var cy = viewport.scrollHeight ? (viewport.scrollTop + viewport.clientHeight / 2) / viewport.scrollHeight : 0.5;

        zoomIndex = index;
        root.style.setProperty('--z', String(ZOOMS[index]));
        root.classList.toggle('is-zoomed', index > 0);
        if (zoomIn) zoomIn.disabled = index === ZOOMS.length - 1;
        if (zoomOut) zoomOut.hidden = index === 0;

        relayout();
        // Размеры книги пересчитываются после события resize: возвращаем центр уже на новом размере
        window.setTimeout(function () {
            if (index === 0) {
                viewport.scrollLeft = 0;
                viewport.scrollTop = 0;
                return;
            }
            centerX();
            viewport.scrollTop = cy * viewport.scrollHeight - viewport.clientHeight / 2;
        }, 120);
    }

    // Перелистнули, пока книга приближена: новая страница (или разворот) снова по центру видимой области
    flip.on('flip', function () { window.setTimeout(centerX, 60); });
    flip.on('changeOrientation', function () { window.setTimeout(centerX, 160); });

    if (zoomIn) zoomIn.addEventListener('click', function () { setZoom(zoomIndex + 1); });
    if (zoomOut) zoomOut.addEventListener('click', function () { setZoom(zoomIndex - 1); });

    // Перетаскивание мышью, пока книга приближена (пальцем прокручивает сам браузер)
    if (viewport) {
        var drag = null;

        viewport.addEventListener('pointerdown', function (event) {
            if (zoomIndex === 0 || event.pointerType !== 'mouse' || event.button !== 0) return;
            drag = { x: event.clientX, y: event.clientY, left: viewport.scrollLeft, top: viewport.scrollTop };
            viewport.classList.add('is-dragging');
            viewport.setPointerCapture(event.pointerId);
        });
        viewport.addEventListener('pointermove', function (event) {
            if (!drag) return;
            viewport.scrollLeft = drag.left - (event.clientX - drag.x);
            viewport.scrollTop = drag.top - (event.clientY - drag.y);
        });
        var stopDrag = function () { drag = null; viewport.classList.remove('is-dragging'); };
        viewport.addEventListener('pointerup', stopDrag);
        viewport.addEventListener('pointercancel', stopDrag);
    }

    // ---- На весь экран и обратно («Свернуть»). Где браузер не умеет настоящий полный экран (iPhone), книга
    // раскрывается во всё окно сама.
    var labels = full ? {
        enter: full.querySelector('[data-flip-full-label="enter"]'),
        exit: full.querySelector('[data-flip-full-label="exit"]'),
    } : {};

    function nativeElement() { return document.fullscreenElement || document.webkitFullscreenElement || null; }
    function isFull() { return nativeElement() === root || root.classList.contains('is-pseudo-full'); }

    function syncFull() {
        var on = isFull();
        root.classList.toggle('is-full', on);
        document.documentElement.classList.toggle('miro-flip-lock', root.classList.contains('is-pseudo-full'));
        if (full) full.setAttribute('aria-pressed', on ? 'true' : 'false');
        if (labels.enter) labels.enter.hidden = on;
        if (labels.exit) labels.exit.hidden = !on;
        relayout();
    }

    function pseudoFull(on) {
        root.classList.toggle('is-pseudo-full', on);
        syncFull();
    }

    function enterFull() {
        var request = root.requestFullscreen || root.webkitRequestFullscreen;
        if (!request) return pseudoFull(true);
        try {
            var result = request.call(root);
            if (result && result.catch) result.catch(function () { pseudoFull(true); });
        } catch (e) { pseudoFull(true); }
    }

    function exitFull() {
        if (root.classList.contains('is-pseudo-full')) return pseudoFull(false);
        var exit = document.exitFullscreen || document.webkitExitFullscreen;
        if (exit) exit.call(document);
    }

    if (full) full.addEventListener('click', function () { isFull() ? exitFull() : enterFull(); });

    // Вышли из полного экрана клавишей Esc или повернули телефон — подпись кнопки и размер книги подстраиваются
    document.addEventListener('fullscreenchange', syncFull);
    document.addEventListener('webkitfullscreenchange', syncFull);

    document.addEventListener('keydown', function (event) {
        var tag = (event.target && event.target.tagName) || '';
        if (/^(INPUT|TEXTAREA|SELECT)$/.test(tag) || event.metaKey || event.ctrlKey || event.altKey) return;
        if (event.key === 'ArrowRight') flip.flipNext();
        else if (event.key === 'ArrowLeft') flip.flipPrev();
        else if (event.key === '+' || event.key === '=') setZoom(zoomIndex + 1);
        else if (event.key === '-' || event.key === '_') setZoom(zoomIndex - 1);
        else if (event.key === 'Escape' && root.classList.contains('is-pseudo-full')) exitFull();
    });
})();
