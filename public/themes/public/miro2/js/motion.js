(() => {
    'use strict';

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    const addAmbientAccents = () => {
        const surfaces = [
            '.miro-about-hero',
            '.miro-members-section',
            '.miro-events-section',
            '.miro-partners-section',
            '.miro-contact-section',
        ];

        surfaces.forEach((selector) => {
            document.querySelectorAll(selector).forEach((surface) => {
                if (surface.querySelector(':scope > .miro-floating-accents')) return;

                surface.classList.add('miro-motion-surface');
                const accents = document.createElement('div');
                accents.className = 'miro-floating-accents';
                accents.setAttribute('aria-hidden', 'true');
                accents.innerHTML = '<span></span><span></span><span></span>';
                surface.prepend(accents);
            });
        });
    };

    // Медленное «проявление» блоков при прокрутке убрано: оно создавало впечатление долгой загрузки, а на телефонах блоки
    // могли так и остаться невидимыми. Контент показывается сразу; здесь остались только мягкие акценты фона.
    const start = () => {
        if (reducedMotion.matches) return;

        addAmbientAccents();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start, { once: true });
    } else {
        start();
    }
})();
