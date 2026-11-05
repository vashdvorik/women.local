Женщина Года 2025: сайт  https://gala2025.innovation.md/
=====================================================================

Самостоятельный статичный сайт: чистые HTML, CSS и JS. Laravel, PHP и node на сервере не нужны.
Папка лежит в git вместе с основным проектом: после «git pull» на хостинге достаточно направить домен gala2025.innovation.md
в эту папку (cPanel → «Домены»/«Поддомены» → корень документов = путь к папке gala2025). Больше ничего настраивать не нужно.

Состав:
    index.html, assets/ (style.css, script.js, img/, catalog/, flip.js, page-flip.browser.js), .htaccess, robots.txt, sitemap.xml

Как менять
----------
Файлы index.html и assets/style.css, script.js СОБИРАЮТСЯ скриптом: править их вручную не нужно (правки пропадут при следующей сборке).
Тексты и факты: tools/gala-sites/data/2025.mjs (каждая фраза на ru, en, ro).
Общий дизайн и анимации: tools/gala-sites/src/style.css и script.js (единые для трёх сайтов).
Сборка из корня проекта:   node tools/gala-sites/build.mjs
Картинки (фото церемоний, страницы журналов) подготавливает:   php tools/gala-sites/prepare-media.php
После сборки закоммитьте папки gala2024, gala2025, gala2026.
