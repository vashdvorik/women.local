(function () {
    'use strict';

    // Бережём процессор и батарею. Декор страницы (мерцающие точки, плавающие значки, переписка с ботом) красив, но на слабом
    // телефоне стоит дорого, поэтому:
    //   1. на слабых устройствах (мало ядер или памяти, экономия трафика, медленная сеть) сразу включается режим «lite»:
    //      бесконечные анимации выключены, диалог бота показан готовым;
    //   2. если страница всё же тормозит (в первые секунды кадры идут медленнее ~20 в секунду), «lite» включается сам;
    //   3. анимации секции ставятся на паузу, когда секции не видно, а у скрытой вкладки на паузе все.
    // Стили режима и паузы лежат в css/navigation.css (.lite, .is-offscreen, .tab-hidden).
    var root = document.documentElement;
    var nav = navigator;
    var conn = nav.connection || {};

    var weak = (nav.hardwareConcurrency && nav.hardwareConcurrency <= 4) ||
        (nav.deviceMemory && nav.deviceMemory <= 2) ||
        conn.saveData === true ||
        /(^|-)2g$/.test(conn.effectiveType || '');
    if (weak) root.classList.add('lite');

    document.addEventListener('visibilitychange', function () {
        root.classList.toggle('tab-hidden', document.hidden);
    });

    if ('IntersectionObserver' in window) {
        var sections = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                entry.target.classList.toggle('is-offscreen', !entry.isIntersecting);
            });
        }, { rootMargin: '120px 0px' });
        Array.prototype.forEach.call(document.querySelectorAll('main > section, .miro-footer'), function (el) { sections.observe(el); });
    }

    // Замер кадров: 45 кадров подряд на видимой вкладке. Берётся медиана времени кадра (разовые остановки, например при
    // переключении окон, её не искажают): больше 50 мс (меньше ~20 к/с) — включаем lite.
    if (!root.classList.contains('lite') && window.requestAnimationFrame) {
        var gaps = [], last = 0;
        var tick = function (now) {
            if (document.hidden) { gaps = []; last = 0; window.requestAnimationFrame(tick); return; }
            if (last) gaps.push(now - last);
            last = now;
            if (gaps.length < 45) { window.requestAnimationFrame(tick); return; }
            gaps.sort(function (a, b) { return a - b; });
            if (gaps[Math.floor(gaps.length / 2)] > 50) root.classList.add('lite');
        };
        window.setTimeout(function () { window.requestAnimationFrame(tick); }, 1500);
    }
}());
