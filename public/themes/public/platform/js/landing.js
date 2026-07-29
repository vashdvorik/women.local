(function () {
    const LANG_KEY = 'inspair_lang';

    function setLang(lang) {
        if (!['ru', 'en', 'ro'].includes(lang)) lang = 'ru';
        localStorage.setItem(LANG_KEY, lang);
        document.documentElement.lang = lang;
        document.querySelectorAll('.lang-btn').forEach(function (button) {
            const active = button.dataset.locale === lang;
            button.classList.toggle('lang-active', active);
            button.classList.toggle('lang-idle', !active);
        });
    }

    const saved = localStorage.getItem(LANG_KEY);
    const browser = (navigator.language || '').slice(0, 2);
    setLang(saved || (['ru', 'en', 'ro'].includes(browser) ? browser : 'ru'));

    document.querySelectorAll('.lang-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            setLang(button.dataset.locale);
        });
    });

    const mobileToggle = document.getElementById('mobile-toggle');
    const mobileMenu = document.getElementById('mobile-menu');
    if (mobileToggle && mobileMenu) {
        mobileToggle.addEventListener('click', function () {
            mobileMenu.classList.toggle('hidden');
        });
        mobileMenu.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                mobileMenu.classList.add('hidden');
            });
        });
    }

    if (window.Telegram && window.Telegram.WebApp) {
        const tg = window.Telegram.WebApp;
        tg.ready();
        document.querySelectorAll('a[href*="t.me/WomenComBot"], a[href*="t.me/lesnichenkoP"]').forEach(function (link) {
            link.addEventListener('click', function (event) {
                event.preventDefault();
                tg.openTelegramLink(link.href);
            });
        });
    }
})();
