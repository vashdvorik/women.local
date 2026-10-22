(function () {
    'use strict';

    // «Наш вклад в цифрах» на главной: когда схема появляется на экране, линии прорисовываются (CSS, по задержкам
    // из разметки), а цифры считаются от нуля до значения. В разметке цифры уже стоят готовыми, поэтому без скрипта,
    // при «уменьшить анимацию» в системе и в старых браузерах блок просто показан целиком.
    var root = document.querySelector('[data-impact]');
    if (!root) return;

    var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduced || !('IntersectionObserver' in window)) return;

    var counters = Array.prototype.slice.call(root.querySelectorAll('[data-count-to]'));

    function format(n) {
        return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }

    function count(el, to) {
        var duration = 1700;
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

    var stepwise = window.matchMedia('(max-width: 899px)').matches;
    root.classList.add('is-armed');
    if (stepwise) root.classList.add('is-stepwise');
    counters.forEach(function (el) { el.textContent = '0'; });

    var tree = root.querySelector('.miro-impact__tree') || root;
    var observer = new IntersectionObserver(function (entries) {
        if (!entries.some(function (entry) { return entry.isIntersecting; })) return;

        observer.disconnect();
        root.classList.add('is-in');

        if (stepwise) {
            // Телефон: схема длинная, поэтому каждый показатель оживает, когда до него доскроллили.
            var items = Array.prototype.slice.call(root.querySelectorAll('.miro-impact__node, .miro-impact__branch-title'));
            var each = new IntersectionObserver(function (list) {
                list.forEach(function (entry) {
                    if (!entry.isIntersecting) return;
                    each.unobserve(entry.target);
                    entry.target.classList.add('is-shown');
                    var counter = entry.target.querySelector('[data-count-to]');
                    if (counter) count(counter, parseInt(counter.getAttribute('data-count-to'), 10) || 0);
                });
            }, { rootMargin: '0px 0px -10% 0px', threshold: 0 });
            items.forEach(function (item) { each.observe(item); });
            return;
        }

        counters.forEach(function (el) {
            var to = parseInt(el.getAttribute('data-count-to'), 10) || 0;
            var delay = parseInt(el.getAttribute('data-count-delay'), 10) || 0;
            window.setTimeout(function () { count(el, to); }, delay);
        });
    }, { rootMargin: '0px 0px -12% 0px', threshold: 0 });

    observer.observe(tree);
}());
