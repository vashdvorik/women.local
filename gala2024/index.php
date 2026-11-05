<?php

// Запасной вход для хостинга, который ищет только index.php и не открывает index.html: выдаёт ту же страницу.
// Если хостинг и так открывает index.html, этот файл не мешает (стоит в списке после него, см. .htaccess).
header('Content-Type: text/html; charset=utf-8');
readfile(__DIR__.'/index.html');
