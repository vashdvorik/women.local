<?php

/**
 * Ночная резервная копия (команда backup:run, расписание в routes/console.php): база данных и все загруженные на сайт фото и файлы.
 * Копия базы сохраняется на сервере и, если задан BACKUP_RCLONE_REMOTE, отправляется на Google Диск программой rclone;
 * туда же копируются все фото и файлы, которые появляются при работе сайта (uploads, originals, public). Как подключить Google Диск: docs/BACKUP.md.
 */
return [

    // Папка с копиями базы на сервере (внутри storage/app, сайтом наружу не раздаётся)
    'directory' => env('BACKUP_DIRECTORY', storage_path('app/backups')),

    // Сколько последних копий базы хранить на сервере
    'keep_local' => (int) env('BACKUP_KEEP_LOCAL', 7),

    // Сколько дней хранить копии базы на Google Диске (старше удаляются). Фото и файлы на Диске не удаляются никогда.
    'keep_remote_days' => (int) env('BACKUP_KEEP_REMOTE_DAYS', 30),

    // Программа mysqldump (если не в PATH, полный путь, например /usr/bin/mysqldump)
    'mysqldump' => env('BACKUP_MYSQLDUMP', 'mysqldump'),

    'rclone' => [
        // Программа rclone (полный путь, если лежит не в PATH, например /home/tirastyl/bin/rclone)
        'binary' => env('BACKUP_RCLONE_BINARY', 'rclone'),

        // «Удалённый диск» из настройки rclone и папка на нём, например gdrive:WomenHubBackups.
        // Пока пусто, копия базы остаётся только на сервере, а фото и файлы никуда не отправляются.
        'remote' => env('BACKUP_RCLONE_REMOTE'),

        // Файл настроек rclone; пусто = стандартный (~/.config/rclone/rclone.conf)
        'config' => env('BACKUP_RCLONE_CONFIG'),
    ],

    // Если копия не удалась, письмо уходит на почту команды (админка → Настройки сайта → Почта)
    'notify_on_failure' => (bool) env('BACKUP_NOTIFY_ON_FAILURE', true),

];
