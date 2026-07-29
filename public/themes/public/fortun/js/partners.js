(() => {
            const root = document.documentElement;
            const nav = document.getElementById('miro-nav');
            const toggle = document.getElementById('miro-mobile-toggle');
            const localeButtons = document.querySelectorAll('[data-locale]');
            const getLocale = () => localStorage.getItem('miro-locale') || 'ru';
            const setLocale = (locale) => {
                root.lang = locale;
                localStorage.setItem('miro-locale', locale);
                localeButtons.forEach((button) => button.classList.toggle('is-active', button.dataset.locale === locale));
            };
            setLocale(getLocale());
            localeButtons.forEach((button) => button.addEventListener('click', () => setLocale(button.dataset.locale)));
            toggle?.addEventListener('click', () => {
                const open = nav.classList.toggle('is-open');
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
        })();
