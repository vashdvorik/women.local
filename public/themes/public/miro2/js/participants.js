// Карусель «Участницы платформы» на главной.
// Листание — нативное: CSS scroll-snap (landing.css), палец и трекпад работают без этого файла.
// Здесь только то, чего CSS не умеет: стрелки, точки, клавиши и заблаговременная подгрузка фото.
(() => {
    'use strict';

    const root = document.querySelector('[data-participants]');
    if (!root) return;

    const track = root.querySelector('[data-participants-track]');
    const prev = root.querySelector('[data-participants-prev]');
    const next = root.querySelector('[data-participants-next]');
    const dotsBox = root.querySelector('[data-participants-dots]');
    const status = root.querySelector('[data-participants-status]');
    const cards = Array.from(track.children);
    if (!cards.length) return;

    const MAX_DOTS = 7;
    const PAGE_WORD = { ru: 'Страница', en: 'Page', ro: 'Pagina' };
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    let perPage = 1; // карточек на странице: --cols * --rows из CSS
    let stride = 0; // px от начала одной страницы до начала следующей
    let pages = 1;
    let page = -1; // текущая страница; -1 — ещё не считали
    let width = 0;
    let dots = [];
    let preloading = false; // фото тянем заранее, только когда карусель уже близко к экрану
    let frame = 0;

    const word = () => PAGE_WORD[document.documentElement.lang] || PAGE_WORD.ru;
    const maxScroll = () => track.scrollWidth - track.clientWidth;

    const measure = () => {
        const style = getComputedStyle(track);
        const cols = parseInt(style.getPropertyValue('--cols'), 10) || 1;
        const rows = parseInt(style.getPropertyValue('--rows'), 10) || 1;

        perPage = cols * rows;
        stride = cards[perPage] ? cards[perPage].offsetLeft - cards[0].offsetLeft : track.clientWidth;
        pages = Math.max(1, Math.ceil(cards.length / perPage));
        width = track.clientWidth;
    };

    // Последняя страница может быть неполной и не дотягиваться до своей точки привязки: у правого края считаем её текущей.
    const current = () => {
        if (track.scrollLeft >= maxScroll() - 2) return pages - 1;

        return Math.min(pages - 1, Math.round(track.scrollLeft / stride));
    };

    const go = (target) => {
        const index = Math.max(0, Math.min(pages - 1, target));

        // Дальний прыжок (точка вдали, End) — мгновенно: без пролёта через все страницы и без лишней подгрузки их фото.
        const far = Math.abs(index - current()) > 1;

        track.scrollTo({
            left: Math.min(index * stride, maxScroll()),
            behavior: reduceMotion.matches || far ? 'auto' : 'smooth',
        });
    };

    // Фото на странице [from, from + count): ленивая загрузка браузера в горизонтальной ленте срабатывает
    // только когда карточка уже показалась, поэтому следующую страницу переводим в eager сами.
    const preload = (from, count) => {
        const end = Math.min(cards.length, (from + count) * perPage);

        for (let i = from * perPage; i < end; i++) {
            const img = cards[i].querySelector('img[loading="lazy"]');
            if (img) img.loading = 'eager';
        }
    };

    // Точек не больше MAX_DOTS: страниц много (28 на телефоне), поэтому окно скользит вслед за текущей.
    const buildDots = () => {
        dotsBox.textContent = '';
        dots = [];

        for (let i = 0; i < (pages > 1 ? Math.min(pages, MAX_DOTS) : 0); i++) {
            const dot = document.createElement('button');
            dot.type = 'button';
            dot.className = 'miro-participants__dot';
            dotsBox.appendChild(dot);
            dots.push(dot);
        }
    };

    const paintDots = () => {
        const shown = dots.length;
        if (!shown) return;

        const start = Math.max(0, Math.min(page - Math.floor(shown / 2), pages - shown));

        dots.forEach((dot, i) => {
            const target = start + i;

            dot.dataset.page = String(target);
            dot.setAttribute('aria-label', `${word()} ${target + 1} / ${pages}`);
            // Крайние точки окна мельче — так видно, что список продолжается за ними.
            dot.classList.toggle('is-edge', (i === 0 && start > 0) || (i === shown - 1 && start + shown < pages));

            if (target === page) dot.setAttribute('aria-current', 'true');
            else dot.removeAttribute('aria-current');
        });
    };

    // aria-disabled, а не disabled: кнопка на краю остаётся в фокусе, и клавиатурный пользователь не теряет место.
    const setDisabled = (button, disabled) => button.setAttribute('aria-disabled', disabled ? 'true' : 'false');

    const update = () => {
        frame = 0;

        setDisabled(prev, track.scrollLeft <= 2);
        setDisabled(next, track.scrollLeft >= maxScroll() - 2);

        const now = current();
        if (now === page) return;

        const announce = page !== -1;
        page = now;
        paintDots();
        if (preloading) preload(page, 2);
        if (announce) status.textContent = `${word()} ${page + 1} / ${pages}`;
    };

    // Сохраняем на экране ту же карточку при повороте телефона или смене брейкпоинта: число карточек на странице меняется.
    const relayout = () => {
        const anchor = Math.max(page, 0) * perPage;

        measure();
        buildDots();
        track.scrollLeft = Math.min(Math.floor(anchor / perPage) * stride, maxScroll());
        page = -1;
        status.textContent = '';
        update();
    };

    prev.addEventListener('click', () => {
        if (prev.getAttribute('aria-disabled') !== 'true') go(current() - 1);
    });

    next.addEventListener('click', () => {
        if (next.getAttribute('aria-disabled') !== 'true') go(current() + 1);
    });

    dotsBox.addEventListener('click', (event) => {
        const dot = event.target.closest('[data-page]');
        if (dot) go(Number(dot.dataset.page));
    });

    track.addEventListener('scroll', () => {
        if (!frame) frame = requestAnimationFrame(update);
    }, { passive: true });

    track.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowRight') go(current() + 1);
        else if (event.key === 'ArrowLeft') go(current() - 1);
        else if (event.key === 'Home') go(0);
        else if (event.key === 'End') go(pages - 1);
        else return;

        event.preventDefault();
    });

    new MutationObserver(paintDots).observe(document.documentElement, { attributes: true, attributeFilter: ['lang'] });

    relayout();

    if ('ResizeObserver' in window) {
        new ResizeObserver(() => {
            if (track.clientWidth !== width) relayout();
        }).observe(track);
    } else {
        window.addEventListener('resize', () => {
            if (track.clientWidth !== width) relayout();
        });
    }

    // Секция далеко от начала страницы: до неё фото не грузим вовсе, а когда до неё осталось меньше экрана,
    // забираем текущую и следующую страницы, чтобы первый свайп не «выпрыгивал» пустыми кругами.
    const startPreloading = () => {
        preloading = true;
        preload(Math.max(page, 0), 2);
    };

    if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver((entries) => {
            if (entries.some((entry) => entry.isIntersecting)) {
                io.disconnect();
                startPreloading();
            }
        }, { rootMargin: '400px 0px' });

        io.observe(root);
    } else {
        window.addEventListener('load', startPreloading);
    }
})();
