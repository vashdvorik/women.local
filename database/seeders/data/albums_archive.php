<?php

/**
 * Фотоальбомы организации (AlbumArchiveSeeder): папка с фото, адрес страницы и название на трёх языках.
 * Порядок записей — порядок на странице «Фото» (первый альбом выше всех).
 *
 * 'folder' — папка внутри dizayn/foto. Фото из неё сжимает `php artisan albums:compress-archive` в
 * public/themes/public/miro/images/albums/{slug}/01.webp, 02.webp … (по порядку имён файлов).
 *
 * Названия собственных имён (NRC, SDC, ATIK…) одинаковы на всех языках. Даты у альбомов не показываются: порядок
 * задаётся датой публикации, которую сидер расставляет по этому списку, а потом её можно менять в админке.
 */
return [
    [
        'slug' => 'nrc',
        'folder' => 'NRC',
        'title' => ['ru' => 'NRC', 'en' => 'NRC', 'ro' => 'NRC'],
    ],
    [
        'slug' => 'sdc',
        'folder' => 'SDC',
        'title' => ['ru' => 'SDC', 'en' => 'SDC', 'ro' => 'SDC'],
    ],
    [
        'slug' => 'un-women',
        'folder' => 'UN Women',
        'title' => ['ru' => 'UN Women', 'en' => 'UN Women', 'ro' => 'UN Women'],
    ],
    [
        'slug' => 'gala-premiya-2025',
        'folder' => 'Гала Премия/2025',
        'title' => ['ru' => 'Гала Премия 2025', 'en' => 'Gala Award 2025', 'ro' => 'Gala Premiilor 2025'],
    ],
    [
        'slug' => 'gala-premiya-biznes-forum',
        'folder' => 'Гала Премия/бизнес форум',
        'title' => ['ru' => 'Гала Премия. Бизнес форум', 'en' => 'Gala Award. Business Forum', 'ro' => 'Gala Premiilor. Forumul de afaceri'],
    ],
    [
        'slug' => 'belyy-shum',
        'folder' => 'Белый шум',
        'title' => ['ru' => 'Белый шум', 'en' => 'White Noise', 'ro' => 'Zgomot alb'],
    ],
    [
        'slug' => 'atik',
        'folder' => 'ATIK',
        'title' => ['ru' => 'ATIK', 'en' => 'ATIK', 'ro' => 'ATIK'],
    ],
    [
        'slug' => 'helvetas',
        'folder' => 'Helvetas',
        'title' => ['ru' => 'Helvetas', 'en' => 'Helvetas', 'ro' => 'Helvetas'],
    ],
    [
        'slug' => 'gala-premiya-2024',
        'folder' => 'Гала Премия/2024',
        'title' => ['ru' => 'Гала Премия 2024', 'en' => 'Gala Award 2024', 'ro' => 'Gala Premiilor 2024'],
    ],
];
