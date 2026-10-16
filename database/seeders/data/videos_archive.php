<?php

/**
 * Видео организации (VideoArchiveSeeder): ролики YouTube страницы «Видео». Порядок записей — порядок на странице
 * (первое видео выше всех). Названия на русском — заголовки роликов на канале (из публичного oEmbed YouTube), на
 * английском и румынском — переводы; поменять их можно в админке («Медиатека → Видео»).
 *
 * Дат у видео нет: на странице они не показываются. Обложка — миниатюра YouTube, своей картинки не требуется.
 */
return [
    [
        'youtube_id' => '5GXgqcI4mjA',
        'title' => [
            'ru' => 'Рады объявить о начале работы SMM школы: 13 сентября стартует первый бесплатный курс-марафон!',
            'en' => 'We are glad to announce the launch of the SMM school: the first free marathon course starts on September 13!',
            'ro' => 'Ne bucurăm să anunțăm lansarea școlii de SMM: pe 13 septembrie începe primul curs-maraton gratuit!',
        ],
    ],
    [
        'youtube_id' => 'BI5HayZ9EQM',
        'title' => ['ru' => 'SMM School', 'en' => 'SMM School', 'ro' => 'Școala de SMM'],
    ],
    [
        'youtube_id' => 'umt-JeAVjkM',
        'title' => ['ru' => 'Женский форум 2022', 'en' => 'Women’s Forum 2022', 'ro' => 'Forumul Femeilor 2022'],
    ],
    [
        'youtube_id' => 'rrco24tGM34',
        'title' => [
            'ru' => 'Организована бизнес-школа для женщин-беженцев из Украины',
            'en' => 'A business school for women refugees from Ukraine has been organized',
            'ro' => 'A fost organizată o școală de business pentru femeile refugiate din Ucraina',
        ],
    ],
    [
        'youtube_id' => '5OF7906EtZs',
        'title' => [
            'ru' => 'Проведена СММ школа для женщин-беженок из Украины',
            'en' => 'An SMM school for women refugees from Ukraine has been held',
            'ro' => 'A avut loc școala de SMM pentru femeile refugiate din Ucraina',
        ],
    ],
    [
        'youtube_id' => 'Uq4YKeR5_-o',
        'title' => ['ru' => 'Школа Женского Лидерства!', 'en' => 'Women’s Leadership School!', 'ro' => 'Școala Liderismului Feminin!'],
    ],
    [
        'youtube_id' => '5B9TPEPOCEU',
        'title' => ['ru' => 'Проект AFAM!', 'en' => 'The AFAM project!', 'ro' => 'Proiectul AFAM!'],
    ],
    [
        'youtube_id' => 'AcesUVGyAkU',
        'title' => [
            'ru' => 'Гала премия «Женщина Года 2025»',
            'en' => 'Gala Award “Woman of the Year 2025”',
            'ro' => 'Gala premiilor „Femeia Anului 2025”',
        ],
    ],
    [
        'youtube_id' => '1t02PdPSAXg',
        'title' => [
            'ru' => 'Финальное мероприятие проекта «Женское лидерство»',
            'en' => 'Final event of the “Women’s Leadership” project',
            'ro' => 'Evenimentul final al proiectului „Leadership feminin”',
        ],
    ],
];
