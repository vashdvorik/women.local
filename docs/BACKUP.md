# Резервная копия: база данных и все загруженные на сайт фото и файлы на Google Диск

Каждую ночь в 03:30 (через cron `schedule:run`, как и остальные задачи) команда `php artisan backup:run`:

1. делает дамп базы (`mysqldump`), сжимает его в `women-hub_ГГГГ-ММ-ДД_ЧЧММСС.sql.gz` и кладёт в `storage/app/backups`;
2. оставляет на сервере 7 последних копий (`BACKUP_KEEP_LOCAL`);
3. отправляет копию базы на Google Диск в папку `<remote>/database` и удаляет там копии старше 30 дней (`BACKUP_KEEP_REMOTE_DAYS`);
4. копирует на Google Диск все файлы, которые появляются при работе сайта (только новые и изменившиеся; удалённые у нас на Диске остаются):
   - `public/uploads` → `<remote>/files/uploads`: фото и файлы, загруженные администратором (новости, альбомы, журналы, каталоги);
   - `storage/app/private/originals` → `<remote>/files/originals`: полные исходники этих фото (по ним перекадрируют картинки);
   - `storage/app/public` → `<remote>/files/public`: фото участниц (`avatars`) и прочее на диске public;
5. при любой ошибке отправляет письмо на почту команды (админка → «Настройки сайта» → «Почта»).

Код сайта, `.env` и системные файлы не копируются: код есть в git, а `.env` храните отдельно (в нём пароли).

**Первый раз.** Загрузка всех накопленных файлов (около 600 МБ) занимает долго. Запустите `php artisan backup:run` вручную по SSH один раз и дождитесь конца (лучше в `screen` или с `nohup`), чтобы ночью cron хостинга не оборвал процесс. Потом каждую ночь отправляется только новое.

## Подключение Google Диска (один раз)

1. На сервере (SSH) установить rclone в `~/bin` (без прав администратора):
   `mkdir -p ~/bin && cd ~/bin && curl -O https://downloads.rclone.org/rclone-current-linux-amd64.zip && unzip -o rclone-current-linux-amd64.zip && cp rclone-*-linux-amd64/rclone ~/bin/rclone && chmod +x ~/bin/rclone && ~/bin/rclone version`
2. На компьютере скачать rclone для Windows и выполнить `rclone authorize "drive"`. Откроется браузер: войти в аккаунт Google и разрешить доступ. Скопировать выданный ключ `{"access_token": ...}`.
3. На сервере `~/bin/rclone config`: новый remote с именем `gdrive`, тип `drive`, `client_id` и `client_secret` пустые (свои не вводить: с приложением Google в режиме «Тестирование» ключ живёт 7 дней), `scope` = `3` (drive.file: rclone видит только созданные им файлы), на вопрос про автоматическую авторизацию в браузере ответить `n` и вставить ключ из шага 2.
4. Проверить: `~/bin/rclone mkdir gdrive:WomenHubBackups && ~/bin/rclone lsd gdrive:`, затем `chmod 600 ~/.config/rclone/rclone.conf`.
5. В `.env` на сервере:
   ```
   BACKUP_RCLONE_BINARY=/home/tirastyl/bin/rclone
   BACKUP_RCLONE_REMOTE=gdrive:WomenHubBackups
   ```
   и `php artisan config:clear`.
6. Проверить вручную: `php artisan backup:run`. Копия должна появиться в `storage/app/backups` и на Google Диске в `WomenHubBackups/database`. Без Google Диска: `php artisan backup:run --no-upload`.

Если `mysqldump` лежит не в PATH, укажите путь в `BACKUP_MYSQLDUMP` (узнать: `which mysqldump`).

## Как восстановить

Сжатая копия базы распаковывается и загружается обратно в MySQL (на пустую или временную базу, не поверх рабочей, пока не убедились в содержимом):
`gunzip -c women-hub_2026-11-11_033000.sql.gz | mysql -u ПОЛЬЗОВАТЕЛЬ -p ИМЯ_БАЗЫ`
Файлы возвращаются обратно командой `rclone copy gdrive:WomenHubBackups/files/uploads public/uploads` (и так же для `originals` в `storage/app/private/originals` и `public` в `storage/app/public`).

## Безопасность

В копии есть почты и телефоны участниц: Google-аккаунт, на который она уходит, должен быть с двухэтапной защитой. Файл `~/.config/rclone/rclone.conf` даёт доступ к папке резервных копий: права `600`, в репозиторий не попадает.
