<?php

/**
 * Каталоги с перелистыванием (CatalogSeeder): журналы организации. Страницы-картинки (WebP) и исходный PDF лежат в
 * database/seeders/data/catalogs/{folder}/ (001.webp, 002.webp, … и catalog.pdf): сервер PDF не разбирает, страницы из
 * него один раз сделал браузер (так же, как это делает форма «Добавить каталог» в админке).
 *
 * Порядок записей — порядок в списке «Публикации»: первый каталог идёт первым. Названия и описания на трёх языках можно
 * править в админке («Медиатека → Каталоги»).
 */
return [
    [
        'slug' => 'zhenskiy-zhurnal-2025',
        'folder' => 'zhenskiy-zhurnal-2025',
        'ratio' => 0.7129,
        'title' => [
            'ru' => 'Женский журнал 2025 — выпуск 2',
            'en' => 'Women’s Magazine 2025 — Issue 2',
            'ro' => 'Revista Femeilor 2025 — ediția 2',
        ],
        'description' => [
            'ru' => 'Второй выпуск журнала 2025 года: истории участниц, события и проекты платформы.',
            'en' => 'The second issue of the 2025 magazine: members’ stories, events and projects of the platform.',
            'ro' => 'A doua ediție a revistei din 2025: poveștile participantelor, evenimentele și proiectele platformei.',
        ],
    ],
    [
        'slug' => 'zhenskiy-zhurnal-2024',
        'folder' => 'zhenskiy-zhurnal-2024',
        'ratio' => 0.8519,
        'title' => [
            'ru' => 'Женский журнал 2024',
            'en' => 'Women’s Magazine 2024',
            'ro' => 'Revista Femeilor 2024',
        ],
        'description' => [
            'ru' => 'Журнал 2024 года: истории участниц, события и проекты платформы.',
            'en' => 'The 2024 magazine: members’ stories, events and projects of the platform.',
            'ro' => 'Revista din 2024: poveștile participantelor, evenimentele și proiectele platformei.',
        ],
    ],
];
