(function () {
    'use strict';

    // Переписка с ботом на первом экране главной: три сценария по кругу. В каждом сообщение участницы, «печатает…», ответ бота
    // и карточка совпадения выходят по очереди, держатся несколько секунд и гаснут, затем начинается следующий сценарий.
    // Всё нарисовано заранее: скрипт лишь добавляет классы по таймерам (между шагами он спит), а браузер анимирует прозрачность
    // и сдвиг готовых блоков. Пока скрипт не «взвёл» блок, при «уменьшить анимацию» в системе и в режиме lite (js/perf.js)
    // показан первый диалог целиком. Когда первый экран не виден или вкладка скрыта, таймеры остановлены.
    var root = document.querySelector('[data-hero-chat]');
    if (!root) return;

    var page = document.documentElement;
    var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduced || !('IntersectionObserver' in window)) return;

    var scenes = Array.prototype.slice.call(root.querySelectorAll('.miro-hero-chat__scene'));
    if (!scenes.length) return;

    var timers = [];
    var current = 0;
    var visible = true;

    function later(fn, ms) {
        timers.push(window.setTimeout(fn, ms));
    }

    function clearTimers() {
        timers.forEach(window.clearTimeout);
        timers = [];
    }

    function reset(scene) {
        scene.classList.remove('is-leaving');
        Array.prototype.forEach.call(scene.querySelectorAll('.miro-hero-chat__msg'), function (msg) {
            msg.classList.remove('is-on', 'is-typing');
        });
    }

    function showStatic() {
        clearTimers();
        scenes.forEach(function (s, i) { s.classList.toggle('is-active', i === 0); });
        Array.prototype.forEach.call(scenes[0].querySelectorAll('.miro-hero-chat__msg'), function (msg) {
            msg.classList.add('is-on');
            msg.classList.remove('is-typing');
        });
    }

    function play(index) {
        if (page.classList.contains('lite')) { showStatic(); return; }

        var scene = scenes[index];
        var msgs = scene.querySelectorAll('.miro-hero-chat__msg');
        var question = msgs[0], answer = msgs[1], card = msgs[2];

        reset(scene);
        scenes.forEach(function (s) { s.classList.toggle('is-active', s === scene); });

        later(function () { question.classList.add('is-on'); }, 400);
        later(function () { answer.classList.add('is-on', 'is-typing'); }, 1500);
        later(function () { answer.classList.remove('is-typing'); }, 2700);
        later(function () { card.classList.add('is-on'); }, 3700);
        // Сцена гаснет целиком (сообщения остаются на месте и плавно теряют прозрачность), затем показывается следующая
        later(function () { scene.classList.add('is-leaving'); scene.classList.remove('is-active'); }, 10200);
        later(function () {
            reset(scene);
            current = (index + 1) % scenes.length;
            play(current);
        }, 10700);
    }

    // Блок скрыт стилями (узкое или низкое окно): таймеры не нужны
    function shown() {
        return root.getClientRects().length > 0;
    }

    function start() {
        clearTimers();
        if (shown()) play(current);
    }

    scenes.forEach(reset);
    root.classList.add('is-armed');

    var observer = new IntersectionObserver(function (entries) {
        visible = entries.some(function (entry) { return entry.isIntersecting; });
        if (visible && !document.hidden) { start(); } else { clearTimers(); }
    }, { threshold: 0.15 });
    observer.observe(root.closest('.miro-hero') || root);

    var resizeTimer = 0;
    window.addEventListener('resize', function () {
        window.clearTimeout(resizeTimer);
        resizeTimer = window.setTimeout(function () { if (visible && !document.hidden) start(); }, 250);
    });

    document.addEventListener('visibilitychange', function () {
        if (document.hidden) { clearTimers(); } else if (visible) { start(); }
    });
}());
