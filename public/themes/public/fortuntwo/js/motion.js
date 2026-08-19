(() => {
    'use strict';

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    const selectors = {
        hero: [
            '.miro-hero--image',
            '.miro-about-hero',
            '.fortun-public-hero',
        ],
        reveal: [
            '.miro-section__head',
            '.fortun-director-note__grid',
            '.miro-logo-wall__layout',
            '.miro-about-split',
            '.miro-about-bridge__inner',
            '.miro-about-cta',
            '.miro-members-cta',
            '.miro-events-cta',
            '.miro-partners-cta',
            '.miro-partners-group__head',
            '.miro-contact-layout > div:first-child',
            '.miro-contact-topics',
            '.fortun-public-cta',
            '.fortun-public-placeholder',
        ],
        stagger: [
            '.fortun-directions__grid',
            '.miro-benefits',
            '.miro-steps',
            '.miro-about-cards',
            '.miro-about-audience',
            '.miro-about-steps',
            '.miro-members-grid',
            '.miro-events-grid',
            '.miro-partners-grid',
            '.miro-contact-cards',
            '.fortun-public-items',
            '.fortun-public-steps',
        ],
    };

    const mark = (type) => selectors[type].forEach((selector) => {
        document.querySelectorAll(selector).forEach((element) => {
            element.dataset.motion = type;
        });
    });

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
                if (surface.querySelector(':scope > .fortun-floating-accents')) return;

                surface.classList.add('fortun-motion-surface');
                const accents = document.createElement('div');
                accents.className = 'fortun-floating-accents';
                accents.setAttribute('aria-hidden', 'true');
                accents.innerHTML = '<span></span><span></span><span></span>';
                surface.prepend(accents);
            });
        });
    };

    const start = () => {
        if (reducedMotion.matches || !('IntersectionObserver' in window)) return;

        mark('hero');
        mark('reveal');
        mark('stagger');
        addAmbientAccents();
        document.documentElement.classList.add('motion-ready');

        const observer = new IntersectionObserver((entries, currentObserver) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;

                entry.target.classList.add('is-visible');
                currentObserver.unobserve(entry.target);
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -36px' });

        document.querySelectorAll('[data-motion]').forEach((element) => observer.observe(element));
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start, { once: true });
    } else {
        start();
    }
})();
