<?php

namespace Tests\Feature;

use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\Visibility;
use ReflectionProperty;
use Tests\TestCase;

/**
 * Права на новые каталоги и файлы в public/uploads.
 *
 * Laravel создаёт каталоги локального диска с правами 0700, если у диска не задано 'visibility' => 'public'.
 * На Windows (разработка) права не действуют, и ошибка не видна, а на Linux-хостинге веб-сервер работает под другим
 * пользователем, чем PHP, и не может войти в такой каталог: файл есть на диске, а картинка не открывается.
 * Каталог нового месяца (public/uploads/ГГГГ/ММ) создаётся заново каждый месяц.
 */
class UploadsPermissionsTest extends TestCase
{
    private function converter(): PortableVisibilityConverter
    {
        $adapter = \Illuminate\Support\Facades\Storage::disk('uploads')->getAdapter();

        $this->assertInstanceOf(LocalFilesystemAdapter::class, $adapter);

        return (new ReflectionProperty(LocalFilesystemAdapter::class, 'visibility'))->getValue($adapter);
    }

    public function test_new_upload_directories_can_be_entered_by_the_web_server(): void
    {
        $this->assertSame(0755, $this->converter()->defaultForDirectories(), 'каталог ГГГГ/ММ должен быть 0755, а не 0700');
        $this->assertSame(0755, $this->converter()->forDirectory(Visibility::PUBLIC));
        $this->assertSame(0755, $this->converter()->forDirectory(Visibility::PRIVATE));
    }

    public function test_new_upload_files_can_be_read_by_the_web_server(): void
    {
        $this->assertSame(0644, $this->converter()->forFile(Visibility::PUBLIC));
        $this->assertSame(0644, $this->converter()->forFile(Visibility::PRIVATE));
    }

    public function test_the_uploads_disk_is_public_like_the_public_disk(): void
    {
        $this->assertSame('public', config('filesystems.disks.uploads.visibility'));
        $this->assertSame('public', config('filesystems.disks.public.visibility'));
    }

    public function test_on_a_unix_machine_a_new_month_directory_really_gets_755(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('На Windows права файлов не действуют.');
        }

        $root = sys_get_temp_dir().'/uploads-perm-'.bin2hex(random_bytes(4));
        $disk = \Illuminate\Support\Facades\Storage::build([...config('filesystems.disks.uploads'), 'root' => $root]);

        $disk->put('2099/12/photo.webp', 'x');

        $this->assertSame('755', substr(sprintf('%o', fileperms($root.'/2099/12')), -3));
        $this->assertSame('644', substr(sprintf('%o', fileperms($root.'/2099/12/photo.webp')), -3));

        $disk->deleteDirectory('2099');
        @rmdir($root);
    }
}
