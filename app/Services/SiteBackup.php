<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Резервная копия: дамп базы (mysqldump, сжатый gzip) и отправка на Google Диск через rclone вместе с фото участниц.
 * Сюда вынесена вся работа с внешними программами; команда backup:run только вызывает шаги по порядку и сообщает о сбое.
 */
class SiteBackup
{
    /** Папка с фото участниц на диске public (см. AvatarStore); копируется вместе со всем диском public. */
    public const AVATARS = 'avatars';

    /** Делает дамп базы в сжатый файл и возвращает путь к нему. */
    public function dumpDatabase(): string
    {
        $connection = (string) config('database.default');
        $db = (array) config("database.connections.{$connection}");

        if (($db['driver'] ?? null) !== 'mysql') {
            throw new RuntimeException('Резервная копия умеет только MySQL, а у сайта подключена база «'.($db['driver'] ?? '?').'».');
        }

        $directory = (string) config('backup.directory');
        if (! is_dir($directory) && ! @mkdir($directory, 0750, true) && ! is_dir($directory)) {
            throw new RuntimeException("Не удалось создать папку для копий: {$directory}");
        }

        $file = $directory.DIRECTORY_SEPARATOR.'women-hub_'.date('Y-m-d_His').'.sql.gz';

        // Пароль передаём через временный файл настроек, а не в командной строке: так он не виден в списке процессов сервера.
        $credentials = tempnam(sys_get_temp_dir(), 'dbc');
        file_put_contents($credentials, "[client]\nuser=\"".addcslashes((string) ($db['username'] ?? ''), "\"\\")."\"\npassword=\"".addcslashes((string) ($db['password'] ?? ''), "\"\\")."\"\nhost=\"".($db['host'] ?? '127.0.0.1')."\"\nport=".(int) ($db['port'] ?? 3306)."\n");
        @chmod($credentials, 0600);

        $gz = gzopen($file, 'wb6');
        if ($gz === false) {
            @unlink($credentials);
            throw new RuntimeException("Не удалось создать файл копии: {$file}");
        }

        $tail = '';
        $process = new Process([
            (string) config('backup.mysqldump'),
            '--defaults-extra-file='.$credentials,
            '--single-transaction', '--quick', '--routines', '--triggers', '--no-tablespaces',
            '--default-character-set=utf8mb4',
            (string) ($db['database'] ?? ''),
        ]);
        $process->setTimeout(1800);

        try {
            $errors = '';
            $process->run(function (string $type, string $buffer) use ($gz, &$tail, &$errors): void {
                if ($type === Process::OUT) {
                    gzwrite($gz, $buffer);
                    $tail = substr($tail.$buffer, -200);
                } else {
                    $errors .= $buffer;
                }
            });
        } finally {
            gzclose($gz);
            @unlink($credentials);
        }

        // mysqldump в конце дампа пишет «Dump completed»: без этой строки копию считаем оборванной
        if (! $process->isSuccessful() || ! str_contains($tail, 'Dump completed')) {
            @unlink($file);

            throw new RuntimeException('mysqldump не завершился: '.trim(mb_substr($errors !== '' ? $errors : $process->getErrorOutput(), 0, 500)));
        }

        return $file;
    }

    /** Оставляет на сервере только $keep последних копий базы; возвращает, сколько удалено. */
    public function pruneLocal(int $keep): int
    {
        $files = glob(rtrim((string) config('backup.directory'), '/\\').DIRECTORY_SEPARATOR.'women-hub_*.sql.gz') ?: [];
        rsort($files); // имя начинается с даты, значит, новые первыми

        $removed = 0;
        foreach (array_slice($files, max(1, $keep)) as $old) {
            $removed += @unlink($old) ? 1 : 0;
        }

        return $removed;
    }

    public function remoteConfigured(): bool
    {
        return filled(config('backup.rclone.remote'));
    }

    /** Отправляет файл базы на Google Диск и убирает там копии старше срока хранения. */
    public function uploadDatabase(string $file): void
    {
        $remote = rtrim((string) config('backup.rclone.remote'), '/');

        $this->rclone(['copyto', $file, $remote.'/database/'.basename($file)]);
        $this->rclone(['delete', $remote.'/database', '--min-age', max(1, (int) config('backup.keep_remote_days')).'d']);
    }

    /**
     * Папки с файлами, которые появляются, пока сайт работает: имя на Google Диске => папка на сервере.
     *  - uploads: фото и файлы, загруженные администратором (новости, альбомы, журналы, каталоги), public/uploads;
     *  - originals: полные исходники этих фото (по ним перекадрируют картинки), закрытая папка storage/app/private/originals;
     *  - public: фото участниц (avatars) и прочее на диске public, storage/app/public.
     *
     * @return array<string, string>
     */
    public function fileFolders(): array
    {
        return [
            'uploads' => (string) config('filesystems.disks.uploads.root'),
            'originals' => (string) config('filesystems.disks.originals.root'),
            'public' => (string) config('filesystems.disks.public.root'),
        ];
    }

    /**
     * Копирует эти папки на Google Диск: отправляются только новые и изменившиеся файлы, а удалённые у нас там остаются.
     * Возвращает, сколько папок отправлено: каких на сервере ещё нет, пропускаются.
     *
     * @return list<string> имена отправленных папок
     */
    public function uploadFiles(): array
    {
        $remote = rtrim((string) config('backup.rclone.remote'), '/');
        $sent = [];

        foreach ($this->fileFolders() as $name => $path) {
            if ($path === '' || ! is_dir($path) || count(scandir($path) ?: []) <= 2) {
                continue;
            }

            $this->rclone(['copy', $path, $remote.'/files/'.$name, '--transfers', '4', '--checkers', '8', '--exclude', '.gitkeep']);
            $sent[] = $name;
        }

        return $sent;
    }

    /** @param list<string> $arguments */
    private function rclone(array $arguments): void
    {
        $command = [(string) config('backup.rclone.binary'), ...$arguments];
        if (filled(config('backup.rclone.config'))) {
            $command[] = '--config='.config('backup.rclone.config');
        }

        $process = new Process($command);
        $process->setTimeout(3600);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException('rclone '.$arguments[0].' не выполнился: '.trim(mb_substr($process->getErrorOutput() ?: $process->getOutput(), 0, 500)));
        }
    }
}
