(function () {
            var supported = ['ru', 'en', 'ro'];
            var saved = localStorage.getItem('miro_lang');
            var browser = (navigator.language || '').slice(0, 2);
            var locale = supported.indexOf(saved) >= 0 ? saved : supported.indexOf(browser) >= 0 ? browser : 'ru';
            var root = document.documentElement;
            var nav = document.getElementById('miro-nav');
            var toggle = document.getElementById('miro-mobile-toggle');

            function setLocale(next) {
                if (supported.indexOf(next) < 0) next = 'ru';
                locale = next;
                root.lang = locale;
                localStorage.setItem('miro_lang', locale);
                document.querySelectorAll('[data-locale]').forEach(function (button) {
                    button.classList.toggle('is-active', button.getAttribute('data-locale') === locale);
                });
            }

            document.querySelectorAll('[data-locale]').forEach(function (button) {
                button.addEventListener('click', function () { setLocale(button.getAttribute('data-locale')); });
            });

            if (toggle) {
                toggle.addEventListener('click', function () {
                    var isOpen = nav.classList.toggle('is-open');
                    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                });
            }

            document.querySelectorAll('.miro-nav__links a').forEach(function (link) {
                link.addEventListener('click', function () {
                    nav.classList.remove('is-open');
                    if (toggle) toggle.setAttribute('aria-expanded', 'false');
                });
            });

            setLocale(locale);

            if (window.Telegram && window.Telegram.WebApp) {
                window.Telegram.WebApp.ready();
            }
        })();
