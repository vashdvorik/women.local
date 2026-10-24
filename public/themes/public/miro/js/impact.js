(function () {
    'use strict';

    // «Наш вклад в цифрах» на главной: когда блок появляется на экране, карточки плавно выходят по очереди, а цифры
    // считаются от нуля до значения. В разметке цифры уже стоят готовыми, поэтому без скрипта, при «уменьшить анимацию»
    // в системе и в старых браузерах блок просто показан целиком.
    var root = document.querySelector('[data-impact]');
    if (!root) return;

    var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduced || !('IntersectionObserver' in window)) return;

    var counters = Array.prototype.slice.call(root.querySelectorAll('[data-count-to]'));

    function format(n) {
        return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }

    function count(el, to) {
        var duration = 1400;
        var started = null;

        function step(now) {
            if (started === null) started = now;
            var progress = Math.min(1, (now - started) / duration);
            var eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = format(Math.round(to * eased));
            if (progress < 1) {
                window.requestAnimationFrame(step);
            } else {
                el.textContent = format(to);
            }
        }

        window.requestAnimationFrame(step);
    }

    root.classList.add('is-armed');
    counters.forEach(function (el) { el.textContent = '0'; });

    var grid = root.querySelector('.miro-impact__grid') || root;
    var observer = new IntersectionObserver(function (entries) {
        if (!entries.some(function (entry) { return entry.isIntersecting; })) return;

        observer.disconnect();
        root.classList.add('is-in');

        counters.forEach(function (el, i) {
            var to = parseInt(el.getAttribute('data-count-to'), 10) || 0;
            window.setTimeout(function () { count(el, to); }, i * 70);
        });
    }, { rootMargin: '0px 0px -12% 0px', threshold: 0 });

    observer.observe(grid);
}());
