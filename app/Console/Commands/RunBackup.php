<?php

namespace App\Console\Commands;

use App\Mail\BackupFailedMail;
use App\Models\SiteSetting;
use App\Services\SiteBackup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Каждую ночь (schedule:run, 03:30): дамп базы данных, сжатый gzip, и фото участниц. Копия базы остаётся на сервере
 * (последние несколько штук) и уходит на Google Диск через rclone; туда же копируются фото участниц. Если что-то
 * не получилось, письмо уходит на почту команды. Подробности и подключение Google Диска: docs/BACKUP.md.
 */
class RunBackup extends Command
{
    protected $signature = 'backup:run {--no-upload : Только копия на сервере, без отправки на Google Диск}';

    protected $description = 'Копия базы данных и фото участниц: на сервер и на Google Диск (rclone)';

    public function handle(SiteBackup $backup): int
    {
        try {
            $file = $backup->dumpDatabase();
            $this->info('База сохранена: '.$file.' ('.round(filesize($file) / 1048576, 2).' МБ)');

            $removed = $backup->pruneLocal((int) config('backup.keep_local'));
            $removed > 0 && $this->line("Старых копий на сервере удалено: {$removed}");

            if ($this->option('no-upload') || ! $backup->remoteConfigured()) {
                $this->warn('Отправка на Google Диск пропущена (не задан BACKUP_RCLONE_REMOTE или указано --no-upload).');

                return self::SUCCESS;
            }

            $backup->uploadDatabase($file);
            $this->info('База отправлена на Google Диск.');

            $folders = $backup->uploadFiles();
            $this->info($folders === [] ? 'Фото и файлы сайта: папки пока пусты, пропущено.' : 'Фото и файлы сайта скопированы на Google Диск: '.implode(', ', $folders).'.');

            Log::info('Backup finished', ['file' => basename($file)]);

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Резервная копия не удалась: '.$e->getMessage());
            Log::error('Backup failed', ['error' => $e->getMessage()]);
            $this->notifyFailure($e->getMessage());

            return self::FAILURE;
        }
    }

    /** Письмо команде о сбое: само по себе сбой почты копию не ломает. */
    private function notifyFailure(string $reason): void
    {
        if (! config('backup.notify_on_failure')) {
            return;
        }

        try {
            // Если сбой как раз в базе, адрес из админки прочитать нельзя: тогда берём адрес из config
            try {
                $to = SiteSetting::contactEmail();
            } catch (Throwable) {
                $to = trim((string) config('site.contacts.email'));
            }

            if ($to === '') {
                return;
            }

            Mail::to($to)->send(new BackupFailedMail($reason));
        } catch (Throwable $e) {
            Log::warning('Backup failure mail not sent', ['error' => $e->getMessage()]);
        }
    }
}
