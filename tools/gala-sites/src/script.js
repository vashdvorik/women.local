/*
 * Сайты гала-премии: язык, шапка, появление при прокрутке, счётчики, пыль на первом экране, лайтбокс, видео.
 * Общий файл для gala2024 / gala2025 / gala2026; собирается tools/gala-sites/build.mjs.
 * Листаемый каталог живёт отдельно (flip.js + page-flip.browser.js).
 */
(function () {
    'use strict';

    var root = document.documentElement;
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    root.classList.remove('no-js');

    // ------------------------------------------------------------------ язык
    // Сохранённый выбор, иначе язык браузера (английский и румынский посетитель сразу видит свой язык), иначе русский.
    var LANGS = ['ru', 'en', 'ro'];
    var i18n = {};
    try { i18n = JSON.parse(document.getElementById('gala-i18n').textContent || '{}'); } catch (e) { i18n = {}; }

    function detectLang() {
        try {
            var saved = localStorage.getItem('gala-lang');
            if (LANGS.indexOf(saved) >= 0) return saved;
        } catch (e) { /* без хранилища просто берём язык браузера */ }
        var list = navigator.languages && navigator.languages.length ? navigator.languages : [navigator.language || 'ru'];
        for (var i = 0; i < list.length; i++) {
            var code = String(list[i]).slice(0, 2).toLowerCase();
            if (LANGS.indexOf(code) >= 0) return code;
        }
        return 'ru';
    }

    function setLang(lang, remember) {
        root.setAttribute('lang', lang);
        document.querySelectorAll('[data-set-lang]').forEach(function (button) {
            button.classList.toggle('is-active', button.getAttribute('data-set-lang') === lang);
            button.setAttribute('aria-pressed', button.getAttribute('data-set-lang') === lang ? 'true' : 'false');
        });
        if (i18n.title && i18n.title[lang]) document.title = i18n.title[lang];
        if (remember) { try { localStorage.setItem('gala-lang', lang); } catch (e) { /* не страшно */ } }
    }

    setLang(detectLang(), false);
    document.querySelectorAll('[data-set-lang]').forEach(function (button) {
        button.addEventListener('click', function () { setLang(button.getAttribute('data-set-lang'), true); });
    });

    // ------------------------------------------------------------------ облегчённый режим
    // Слабое устройство (мало ядер или памяти) или рывки при анимации: убираем пыль, блики и кольца (правила — в конце style.css).
    var conn = navigator.connection || {};
    var weak = (navigator.hardwareConcurrency && navigator.hardwareConcurrency <= 4) || (navigator.deviceMemory && navigator.deviceMemory <= 4) || conn.saveData === true || /(^|-)2g$|3g/.test(conn.effectiveType || '');
    if (weak || reduce) root.classList.add('lite');

    // Первый экран «собирается»: слова заголовка поднимаются, остальное проявляется. Ждать загрузки шрифтов и картинок не нужно:
    // главная картинка уже запрошена в <head> (preload), а текст виден сразу.
    function ready() { root.classList.add('is-ready'); }
    requestAnimationFrame(function () { requestAnimationFrame(ready); });
    setTimeout(ready, 450); // запасной ход: в фоновой вкладке кадры анимации приостанавливаются

    window.addEventListener('load', function () {
        if (reduce || root.classList.contains('lite')) return;
        var frames = 0;
        var start = performance.now();
        (function probe(now) {
            frames++;
            if (now - start < 2000) { requestAnimationFrame(probe); return; }
            if (frames / ((now - start) / 1000) < 45) root.classList.add('lite');
        })(start);
    });

    // ------------------------------------------------------------------ шапка, прогресс, «наверх»
    var nav = document.querySelector('.nav');
    var progress = document.querySelector('.progress');
    var toTop = document.querySelector('.totop');
    var hero = document.querySelector('.hero');
    var heroBg = document.querySelector('.hero__bg');
    var heroContent = document.querySelector('.hero__content');
    var ticking = false;

    function onScroll() {
        ticking = false;
        var y = window.pageYOffset || document.documentElement.scrollTop;
        var max = document.documentElement.scrollHeight - window.innerHeight;
        if (nav) nav.classList.toggle('is-solid', y > 40);
        if (progress && max > 0) progress.style.transform = 'scaleX(' + Math.min(1, y / max).toFixed(4) + ')';
        if (toTop) toTop.classList.toggle('is-shown', y > window.innerHeight * 0.9);
        // Лёгкий параллакс первого экрана (только transform)
        if (hero && !reduce && !root.classList.contains('lite') && window.innerWidth > 720 && window.innerWidth / window.innerHeight > 1.55 && window.innerHeight > 520 && y < window.innerHeight * 1.2) {
            if (heroBg) heroBg.style.transform = 'translate3d(0,' + (y * 0.18).toFixed(1) + 'px,0)';
            if (heroContent) heroContent.style.transform = 'translate3d(0,' + (y * -0.06).toFixed(1) + 'px,0)';
        }
    }
    window.addEventListener('scroll', function () { if (!ticking) { ticking = true; requestAnimationFrame(onScroll); } }, { passive: true });
    onScroll();

    if (toTop) toTop.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' }); });

    var burger = document.querySelector('.nav__burger');
    if (burger && nav) {
        burger.addEventListener('click', function () {
            var open = nav.classList.toggle('is-open');
            burger.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        nav.querySelectorAll('.nav__links a').forEach(function (a) {
            a.addEventListener('click', function () { nav.classList.remove('is-open'); burger.setAttribute('aria-expanded', 'false'); });
        });
    }

    // Подсветка пункта меню по разделу на экране
    var sections = [].slice.call(document.querySelectorAll('main section[id]'));
    var links = [].slice.call(document.querySelectorAll('.nav__links a[href^="#"]'));
    if ('IntersectionObserver' in window && sections.length) {
        var spy = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                links.forEach(function (a) { a.classList.toggle('is-active', a.getAttribute('href') === '#' + entry.target.id); });
            });
        }, { rootMargin: '-45% 0px -50% 0px' });
        sections.forEach(function (s) { spy.observe(s); });
    }

    // ------------------------------------------------------------------ появление при прокрутке и счётчики
    var reveals = [].slice.call(document.querySelectorAll('.reveal'));
    function showAll() { reveals.forEach(function (el) { el.classList.add('in'); }); }

    function count(el) {
        var target = parseFloat(el.getAttribute('data-count'));
        if (isNaN(target)) return;
        if (reduce) { el.textContent = String(target); return; }
        var started = performance.now();
        var duration = 1600;
        (function step(now) {
            var t = Math.min(1, (now - started) / duration);
            var eased = 1 - Math.pow(1 - t, 3);
            el.textContent = String(Math.round(target * eased));
            if (t < 1) requestAnimationFrame(step);
        })(started);
    }

    if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('in');
                entry.target.querySelectorAll('[data-count]').forEach(count);
                if (entry.target.hasAttribute('data-count')) count(entry.target);
                io.unobserve(entry.target);
            });
        }, { threshold: 0.14, rootMargin: '0px 0px -6% 0px' });
        reveals.forEach(function (el) { io.observe(el); });
    } else {
        showAll();
        document.querySelectorAll('[data-count]').forEach(count);
    }

    // ------------------------------------------------------------------ первый экран: пятно за курсором и золотая пыль
    if (hero && window.matchMedia && window.matchMedia('(hover: hover) and (pointer: fine)').matches && !reduce) {
        root.classList.add('has-pointer');
        var mx = window.innerWidth / 2, my = window.innerHeight * 0.4, tx = mx, ty = my, spotTick = false;
        hero.addEventListener('pointermove', function (e) {
            var rect = hero.getBoundingClientRect();
            tx = e.clientX - rect.left; ty = e.clientY - rect.top;
            if (!spotTick) { spotTick = true; requestAnimationFrame(moveSpot); }
        });
        function moveSpot() {
            mx += (tx - mx) * 0.14; my += (ty - my) * 0.14;
            root.style.setProperty('--mx', mx.toFixed(1) + 'px');
            root.style.setProperty('--my', my.toFixed(1) + 'px');
            if (Math.abs(tx - mx) > 0.5 || Math.abs(ty - my) > 0.5) requestAnimationFrame(moveSpot); else spotTick = false;
        }
    }

    var canvas = document.querySelector('.dust');
    if (canvas && !reduce) {
        var ctx = canvas.getContext('2d');
        var w = 0, h = 0, dpr = Math.min(window.devicePixelRatio || 1, 2), motes = [], visible = true, raf = 0;
        var color = getComputedStyle(root).getPropertyValue('--glow').trim() || '219,184,155';

        function resize() {
            w = canvas.clientWidth; h = canvas.clientHeight;
            canvas.width = w * dpr; canvas.height = h * dpr;
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            var n = Math.round(Math.min(70, Math.max(24, w / 22)));
            motes = [];
            for (var i = 0; i < n; i++) {
                motes.push({ x: Math.random() * w, y: Math.random() * h, r: Math.random() * 1.8 + 0.4, v: Math.random() * 0.35 + 0.08, a: Math.random() * 0.6 + 0.2, p: Math.random() * 6.28, s: Math.random() * 0.8 + 0.3 });
            }
        }
        function draw(t) {
            raf = 0;
            if (root.classList.contains('lite')) { ctx.clearRect(0, 0, w, h); return; }
            ctx.clearRect(0, 0, w, h);
            for (var i = 0; i < motes.length; i++) {
                var m = motes[i];
                m.y -= m.v; m.x += Math.sin(t / 1600 * m.s + m.p) * 0.18;
                if (m.y < -4) { m.y = h + 4; m.x = Math.random() * w; }
                var tw = 0.55 + 0.45 * Math.sin(t / 700 * m.s + m.p);
                ctx.beginPath();
                ctx.fillStyle = 'rgba(' + color + ',' + (m.a * tw).toFixed(3) + ')';
                ctx.arc(m.x, m.y, m.r, 0, 6.2832);
                ctx.fill();
            }
            if (visible) raf = requestAnimationFrame(draw);
        }
        resize();
        window.addEventListener('resize', function () { resize(); });
        if ('IntersectionObserver' in window) {
            new IntersectionObserver(function (entries) {
                visible = entries[0].isIntersecting;
                if (visible && !raf) raf = requestAnimationFrame(draw);
            }).observe(hero);
        }
        raf = requestAnimationFrame(draw);
    }

    // ------------------------------------------------------------------ лайтбокс для фото
    var shots = [].slice.call(document.querySelectorAll('[data-shot]'));
    var box = document.querySelector('.lightbox');
    if (shots.length && box) {
        var img = box.querySelector('.lightbox__img');
        var counter = box.querySelector('.lightbox__count');
        var index = 0, lastFocus = null, touchX = null;

        function show(i) {
            index = (i + shots.length) % shots.length;
            img.classList.remove('lightbox__img');
            void img.offsetWidth; // перезапускаем появление
            img.classList.add('lightbox__img');
            img.src = shots[index].getAttribute('data-shot');
            img.alt = shots[index].getAttribute('data-alt') || '';
            counter.textContent = (index + 1) + ' / ' + shots.length;
            // Соседние кадры подгружаем заранее
            [index + 1, index - 1].forEach(function (n) { var p = new Image(); p.src = shots[(n + shots.length) % shots.length].getAttribute('data-shot'); });
        }
        function open(i) { lastFocus = document.activeElement; box.classList.add('is-open'); box.setAttribute('aria-hidden', 'false'); document.body.style.overflow = 'hidden'; show(i); box.querySelector('.lightbox__close').focus(); }
        function close() { box.classList.remove('is-open'); box.setAttribute('aria-hidden', 'true'); document.body.style.overflow = ''; img.removeAttribute('src'); if (lastFocus) lastFocus.focus(); }

        shots.forEach(function (shot, i) { shot.addEventListener('click', function () { open(i); }); });
        box.querySelector('.lightbox__close').addEventListener('click', close);
        box.querySelector('.lightbox__prev').addEventListener('click', function () { show(index - 1); });
        box.querySelector('.lightbox__next').addEventListener('click', function () { show(index + 1); });
        box.addEventListener('click', function (e) { if (e.target === box || e.target.classList.contains('lightbox__stage')) close(); });
        document.addEventListener('keydown', function (e) {
            if (!box.classList.contains('is-open')) return;
            if (e.key === 'Escape') close();
            else if (e.key === 'ArrowRight') show(index + 1);
            else if (e.key === 'ArrowLeft') show(index - 1);
        });
        box.addEventListener('touchstart', function (e) { touchX = e.changedTouches[0].clientX; }, { passive: true });
        box.addEventListener('touchend', function (e) {
            if (touchX === null) return;
            var dx = e.changedTouches[0].clientX - touchX; touchX = null;
            if (Math.abs(dx) > 50) show(index + (dx < 0 ? 1 : -1));
        }, { passive: true });
    }

    // ------------------------------------------------------------------ видео: плеер YouTube загружается только по нажатию
    document.querySelectorAll('[data-video]').forEach(function (poster) {
        poster.addEventListener('click', function () {
            // Страница открыта файлом с диска (file://): у неё нет адреса сайта, и YouTube отвечает ошибкой 153. Открываем ролик на YouTube.
            if (location.protocol === 'file:') { window.open('https://www.youtube.com/watch?v=' + poster.getAttribute('data-video'), '_blank', 'noopener'); return; }
            var frame = poster.parentNode;
            var iframe = document.createElement('iframe');
            iframe.src = 'https://www.youtube-nocookie.com/embed/' + poster.getAttribute('data-video') + '?autoplay=1&rel=0&modestbranding=1';
            iframe.title = poster.getAttribute('data-title') || 'Video';
            iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen';
            iframe.referrerPolicy = 'strict-origin-when-cross-origin';
            iframe.allowFullscreen = true;
            frame.appendChild(iframe);
            poster.remove();
        });
    });
})();
