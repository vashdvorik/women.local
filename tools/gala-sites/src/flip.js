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
    // Телефон и планшет (основное устройство ввода — палец): библиотека листания понимает только быстрый «щелчок» (до 0,25 с)
    // или перетаскивание за угол страницы, поэтому обычный неспешный свайп из середины страницы у неё не срабатывает.
    // На таких устройствах листание ведём сами (ниже): свайп любой скорости и касание краёв страницы.
    var touchOnly = !!(window.matchMedia && window.matchMedia('(pointer: coarse)').matches);
    var AHEAD = touchOnly ? 2 : 3; // сколько страниц вперёд и назад держать загруженными (на телефоне меньше: экономим память)
    var ZOOMS = [1, 1.5, 2, 2.5]; // шаги приближения; страницы в файле крупнее, чем на экране, поэтому текст остаётся чётким
    var MAX_PAGE_WIDTH = 1700; // самая широкая страница при самом сильном приближении
    var MIN_PAGE_WIDTH = 220; // ширина страницы, уже́ которой книга не сжимается; вдвое меньше ширины — одна страница вместо разворота
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
        minWidth: MIN_PAGE_WIDTH,
        maxWidth: MAX_PAGE_WIDTH,
        minHeight: Math.round(MIN_PAGE_WIDTH / ratio),
        maxHeight: Math.round(MAX_PAGE_WIDTH / ratio),
        maxShadowOpacity: 0.4,
        showCover: true,
        // Прокрутка страницы пальцем никогда не блокируется листанием (на телефоне листаем сами, см. ниже)
        mobileScrollSupport: true,
        usePortrait: true,
        // На телефоне листание короче и без теней: каждый кадр анимации там перерисовывает большие картинки
        flippingTime: touchOnly ? 450 : 650,
        drawShadow: !touchOnly,
        useMouseEvents: !touchOnly,
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

        // Одна страница на экране (телефон): стили сжимают лист, чтобы он помещался на первом экране вместе с панелью
        var portrait = !(flip.getOrientation && flip.getOrientation() === 'landscape');
        if (root.classList.contains('is-portrait') !== portrait) {
            root.classList.toggle('is-portrait', portrait);
            window.dispatchEvent(new Event('resize'));
        }
    }

    flip.on('flip', function (event) { update(event.data); });
    flip.on('changeOrientation', function () { update(flip.getCurrentPageIndex()); });
    update(0);

    if (bar) bar.hidden = false;

    // На телефоне и планшете под панелью подсказка, как листать и как прокрутить сайт
    var hint = root.querySelector('[data-flip-hint]');
    if (hint && touchOnly) hint.hidden = false;

    var prev = root.querySelector('[data-flip-prev]');
    var next = root.querySelector('[data-flip-next]');
    var full = root.querySelector('[data-flip-full]');
    var zoomIn = root.querySelector('[data-flip-zoom-in]');
    var zoomOut = root.querySelector('[data-flip-zoom-out]');

    if (prev) prev.addEventListener('click', function () { flip.flipPrev(); });
    if (next) next.addEventListener('click', function () { flip.flipNext(); });

    var fit = root.querySelector('.miro-flip__fit');

    function relayout() { window.dispatchEvent(new Event('resize')); }

    // ---- Приближение листа. Увеличивается сама книга (она растягивается внутри своей рамки, рамка прокручивается), а не
    // сайт: щипок двумя пальцами в рамке не масштабирует страницу браузера (touch-action в стилях), а приближает лист.
    // Кнопки и клавиши идут по шагам ZOOMS, щипок плавный.
    var zoomValue = 1;
    var ZOOM_MAX_TOUCH = 3;

    function isZoomed() { return zoomValue > 1.001; }
    function maxZoom() { return touchOnly ? ZOOM_MAX_TOUCH : ZOOMS[ZOOMS.length - 1]; }

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
        if (!viewport || !isZoomed()) return;
        viewport.scrollLeft = contentCenter() * viewport.scrollWidth - viewport.clientWidth / 2;
    }

    // Книга решает «одна страница или разворот» по ширине колонки: уже 2 × MIN_PAGE_WIDTH — одна страница. При приближении
    // колонка шире, и на телефоне книга сама превратилась бы в разворот с теми же мелкими страницами. Чтобы приближение
    // увеличивало именно страницу, порог на время приближения поднимается вместе с шириной.
    var baseWidth = 0;

    function keepSinglePage() {
        var settings = flip.getSettings && flip.getSettings();
        if (!settings) return;
        settings.minWidth = baseWidth && baseWidth < 2 * MIN_PAGE_WIDTH && isZoomed()
            ? Math.ceil((baseWidth * zoomValue) / 2) + 2
            : MIN_PAGE_WIDTH;
    }

    // focus — точка на экране, которая должна остаться на месте (середина щипка); без неё остаётся центр
    function setZoomValue(value, focus) {
        value = Math.max(1, Math.min(maxZoom(), value));
        if (!viewport || Math.abs(value - zoomValue) < 0.001) return;

        var wasZoomed = isZoomed();
        if (!wasZoomed) {
            baseWidth = book.getBoundingClientRect().width;
            // Рамка остаётся того же размера, что и без приближения: растёт только лист внутри неё, ничего вокруг не сдвигается
            root.style.setProperty('--frame-h', Math.round(viewport.getBoundingClientRect().height) + 'px');
        }

        var rect = viewport.getBoundingClientRect();
        var mx = focus ? focus.x - rect.left : viewport.clientWidth / 2;
        var my = focus ? focus.y - rect.top : viewport.clientHeight / 2;
        var fx = viewport.scrollWidth ? (viewport.scrollLeft + mx) / viewport.scrollWidth : 0.5;
        var fy = viewport.scrollHeight ? (viewport.scrollTop + my) / viewport.scrollHeight : 0.5;

        zoomValue = value;
        keepSinglePage();
        root.style.setProperty('--z', String(value));
        root.classList.toggle('is-zoomed', isZoomed());
        if (!isZoomed()) root.style.removeProperty('--frame-h');
        if (zoomIn) zoomIn.disabled = value >= maxZoom() - 0.001;
        if (zoomOut) zoomOut.disabled = !isZoomed();

        relayout();
        // Размеры книги пересчитываются после события resize: возвращаем нужную точку уже на новом размере
        window.setTimeout(function () {
            if (!isZoomed()) {
                viewport.scrollLeft = 0;
                viewport.scrollTop = 0;
                return;
            }
            if (focus) viewport.scrollLeft = fx * viewport.scrollWidth - mx;
            else centerX();
            viewport.scrollTop = fy * viewport.scrollHeight - my;
        }, 120);

    }

    // Шаг кнопки или клавиши: к ближайшему большему (меньшему) значению из ZOOMS, на телефоне есть ещё ZOOM_MAX_TOUCH
    function stepZoom(direction) {
        var steps = touchOnly ? ZOOMS.concat([ZOOM_MAX_TOUCH]) : ZOOMS;
        var target = direction > 0 ? maxZoom() : 1;

        for (var i = 0; i < steps.length; i++) {
            var step = steps[direction > 0 ? i : steps.length - 1 - i];
            if (direction > 0 && step > zoomValue + 0.01) { target = step; break; }
            if (direction < 0 && step < zoomValue - 0.01) { target = step; break; }
        }

        setZoomValue(target);
    }

    // Перелистнули, пока книга приближена: новая страница (или разворот) снова по центру видимой области
    flip.on('flip', function () { window.setTimeout(centerX, 60); });
    flip.on('changeOrientation', function () { window.setTimeout(centerX, 160); });

    // Размер рамки запоминается при приближении, поэтому после поворота телефона приближение снимается. Свои пересчёты
    // (relayout) не считаются, а смена только высоты окна (прячется адресная строка) не мешает.
    var lastWidth = window.innerWidth;
    window.addEventListener('resize', function (event) {
        if (!event.isTrusted || window.innerWidth === lastWidth) return;
        lastWidth = window.innerWidth;
        if (isZoomed()) setZoomValue(1);
    });

    if (zoomIn) zoomIn.addEventListener('click', function () { stepZoom(1); });
    if (zoomOut) zoomOut.addEventListener('click', function () { stepZoom(-1); });

    // ---- Пальцем (телефон, планшет): свайп влево/вправо любой скорости и касание левой/правой трети страницы листают,
    // вертикальное движение остаётся прокруткой страницы, щипок двумя пальцами приближает лист.
    if (touchOnly && viewport) {
        var pointers = {};
        var gesture = null;
        var pinch = null;
        var pinching = false; // пока после щипка на экране остались пальцы, листать нельзя

        var touchCount = function () { return Object.keys(pointers).length; };
        var spread = function (a, b) { return Math.sqrt((a.x - b.x) * (a.x - b.x) + (a.y - b.y) * (a.y - b.y)) || 1; };
        var twoTouches = function () { var ids = Object.keys(pointers); return ids.length === 2 ? [pointers[ids[0]], pointers[ids[1]]] : null; };

        viewport.addEventListener('pointerdown', function (event) {
            if (event.pointerType === 'mouse') return;
            pointers[event.pointerId] = { x: event.clientX, y: event.clientY };

            if (touchCount() === 1) {
                pinching = false;
                gesture = { x: event.clientX, y: event.clientY, t: Date.now() };
                return;
            }

            gesture = null;
            var two = twoTouches();
            if (!two) return;

            // Второй палец: начался щипок. Лист следует за пальцами сразу (transform), а настоящие размеры книги
            // пересчитываются один раз, когда пальцы отпущены.
            var box = fit.getBoundingClientRect();
            pinching = true;
            pinch = { dist: spread(two[0], two[1]), z: zoomValue, mx: (two[0].x + two[1].x) / 2, my: (two[0].y + two[1].y) / 2, scale: 1 };
            fit.style.transformOrigin = (pinch.mx - box.left) + 'px ' + (pinch.my - box.top) + 'px';
            viewport.classList.add('is-pinching');
        });

        viewport.addEventListener('pointermove', function (event) {
            var point = pointers[event.pointerId];
            if (!point) return;
            point.x = event.clientX;
            point.y = event.clientY;

            var two = pinch && twoTouches();
            if (!two) return;

            pinch.scale = Math.max(1 / pinch.z, Math.min(maxZoom() / pinch.z, spread(two[0], two[1]) / pinch.dist));
            fit.style.transform = 'scale(' + pinch.scale + ')';
        });

        var release = function (event, cancelled) {
            if (!pointers[event.pointerId]) return;
            delete pointers[event.pointerId];

            if (pinch) {
                var done = pinch;
                pinch = null;
                fit.style.transform = '';
                fit.style.transformOrigin = '';
                viewport.classList.remove('is-pinching');
                setZoomValue(Math.round(done.z * done.scale * 100) / 100, { x: done.mx, y: done.my });
            }

            if (touchCount() === 0) pinching = false;

            var start = gesture;
            gesture = null;
            if (cancelled || !start || pinching || isZoomed()) return;

            var dx = event.clientX - start.x;
            var dy = event.clientY - start.y;
            var time = Date.now() - start.t;

            if (Math.abs(dx) >= 36 && Math.abs(dx) > Math.abs(dy) * 1.3 && time < 1000) {
                if (dx < 0) flip.flipNext(); else flip.flipPrev();
            } else if (Math.abs(dx) < 12 && Math.abs(dy) < 12 && time < 450) {
                var rect = book.getBoundingClientRect();
                var where = (event.clientX - rect.left) / (rect.width || 1);
                if (where < 0.33) flip.flipPrev();
                else if (where > 0.67) flip.flipNext();
            }
        };

        viewport.addEventListener('pointerup', function (event) { if (event.pointerType !== 'mouse') release(event, false); });
        // Браузер забрал касание себе (вертикальная прокрутка страницы): листать не нужно
        viewport.addEventListener('pointercancel', function (event) { if (event.pointerType !== 'mouse') release(event, true); });

        // Safari на iPhone масштабирует страницу своим жестом: внутри рамки каталога он отключён
        ['gesturestart', 'gesturechange', 'gestureend'].forEach(function (name) {
            viewport.addEventListener(name, function (event) { event.preventDefault(); }, { passive: false });
        });
    }

    // Перетаскивание мышью, пока книга приближена (пальцем прокручивает сам браузер)
    if (viewport) {
        var drag = null;

        viewport.addEventListener('pointerdown', function (event) {
            if (!isZoomed() || event.pointerType !== 'mouse' || event.button !== 0) return;
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
        if (isZoomed()) setZoomValue(1);
        root.classList.toggle('is-full', on);
        document.documentElement.classList.toggle('miro-flip-lock', root.classList.contains('is-pseudo-full'));
        if (full) full.setAttribute('aria-pressed', on ? 'true' : 'false');
        if (labels.enter) labels.enter.hidden = on;
        if (labels.exit) labels.exit.hidden = !on;
        relayout();
    }

    // «Во всё окно» работает через position: fixed, но предок со своим слоем (position и z-index) держит книгу под шапкой
    // сайта, и она закрывает верх экрана. Поэтому на время раскрытия z-index таких предков снимается.
    var lifted = [];

    function liftAncestors(on) {
        lifted.forEach(function (item) { item.el.style.zIndex = item.value; });
        lifted = [];
        if (!on) return;

        for (var el = root.parentElement; el && el !== document.documentElement; el = el.parentElement) {
            var style = window.getComputedStyle(el);
            if (style.position !== 'static' && style.zIndex !== 'auto') {
                lifted.push({ el: el, value: el.style.zIndex });
                el.style.zIndex = 'auto';
            }
        }
    }

    function pseudoFull(on) {
        liftAncestors(on);
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
        else if (event.key === '+' || event.key === '=') stepZoom(1);
        else if (event.key === '-' || event.key === '_') stepZoom(-1);
        else if (event.key === 'Escape' && root.classList.contains('is-pseudo-full')) exitFull();
    });
})();
