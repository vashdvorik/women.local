<?php

namespace Tests\Feature;

use App\Mail\BackupFailedMail;
use App\Models\SiteSetting;
use App\Services\SiteBackup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Ночная копия (backup:run): на сервере остаются только последние копии базы, а сбой не проходит молча: команда падает
 * и письмо уходит на почту команды. Сам mysqldump и rclone в тестах не запускаются (в тестах база sqlite).
 */
class BackupTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_the_latest_database_copies_stay_on_the_server(): void
    {
        $dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'backup-test-'.uniqid();
        mkdir($dir);
        config(['backup.directory' => $dir]);

        foreach (['2026-11-01_033000', '2026-11-02_033000', '2026-11-03_033000', '2026-11-04_033000'] as $stamp) {
            file_put_contents("{$dir}/women-hub_{$stamp}.sql.gz", 'x');
        }
        file_put_contents("{$dir}/other.txt", 'не копия');

        $removed = app(SiteBackup::class)->pruneLocal(2);

        $left = array_map('basename', glob("{$dir}/*"));
        sort($left);
        $this->assertSame(2, $removed);
        $this->assertSame(['other.txt', 'women-hub_2026-11-03_033000.sql.gz', 'women-hub_2026-11-04_033000.sql.gz'], $left);

        array_map('unlink', glob("{$dir}/*"));
        rmdir($dir);
    }

    public function test_every_folder_the_admin_and_the_site_write_files_to_is_backed_up(): void
    {
        $folders = app(SiteBackup::class)->fileFolders();

        // Загрузки администратора (public/uploads), исходники фото (private/originals) и диск public (фото участниц)
        $this->assertSame(['uploads', 'originals', 'public'], array_keys($folders));
        $this->assertSame(public_path('uploads'), $folders['uploads']);
        $this->assertSame(storage_path('app/private/originals'), $folders['originals']);
        $this->assertSame(storage_path('app/public'), $folders['public']);
    }

    public function test_a_failed_backup_exits_with_an_error_and_mails_the_team(): void
    {
        Mail::fake();
        SiteSetting::setContactEmail('team@example.test');

        // В тестах база sqlite: копия умеет только MySQL и должна упасть с понятной причиной
        $this->artisan('backup:run')->assertFailed();

        Mail::assertSent(BackupFailedMail::class, fn (BackupFailedMail $mail) => $mail->hasTo('team@example.test')
            && str_contains($mail->render(), 'только MySQL'));
    }

    public function test_without_a_remote_the_cloud_upload_is_off_and_the_schedule_has_the_command(): void
    {
        config(['backup.rclone.remote' => null]);
        $this->assertFalse(app(SiteBackup::class)->remoteConfigured());

        config(['backup.rclone.remote' => 'gdrive:WomenHubBackups']);
        $this->assertTrue(app(SiteBackup::class)->remoteConfigured());

        $this->artisan('schedule:list')->expectsOutputToContain('backup:run')->assertSuccessful();
    }
}
