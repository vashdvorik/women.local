<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

/**
 * Название платформы — «Women's Hub». Прежнее «Women Entrepreneurs Platform of the Two Banks» (и его сокращённый
 * вид) нигде не должно остаться: ни в шаблонах, ни в текстах бота, ни в языковых файлах, ни в демо-данных.
 *
 * Не считается названием и потому разрешено: «Platform for Women Entrepreneurs» и «Платформа женщин предпринимателей» как
 * подзаголовок-пояснение под заголовком лендинга, слоганы о «двух берегах» и общая формулировка «a women entrepreneurs
 * platform» в подсказке для AI.
 */
class BrandNameTest extends TestCase
{
    use RefreshDatabase;

    private const DIRECTORIES = ['app', 'resources', 'lang', 'database', 'routes', 'config', 'tests/Feature/Telegram'];

    private const OLD_NAMES = '/Women Entrepreneurs Platform|(?i:of the Two Banks)/';

    public function test_the_old_platform_name_is_nowhere_in_the_sources(): void
    {
        $found = [];

        foreach (self::DIRECTORIES as $directory) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(base_path($directory), RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
                $path = (string) $file;

                if (! preg_match('/\.(php|json|js|css|html|txt|md)$/', $path) || str_contains($path, DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR)) {
                    continue;
                }

                if (str_ends_with($path, 'BrandNameTest.php')) {
                    continue;
                }

                if (preg_match(self::OLD_NAMES, (string) file_get_contents($path), $m)) {
                    $found[] = str_replace('\\', '/', substr($path, strlen(base_path()) + 1)).' — «'.$m[0].'»';
                }
            }
        }

        $this->assertSame([], $found, 'Старое название платформы: замените на «Women\'s hub»');
    }

    public function test_the_public_site_uses_the_new_name_in_titles_and_footer(): void
    {
        $landing = str_replace(chr(194).chr(160), ' ', $this->get('/')->assertOk()->getContent()); // неразрывные пробелы в названиях — для глаза, текст тот же

        $this->assertStringContainsString("<title>Women's Hub</title>", $landing);
        $this->assertStringContainsString("© ".date('Y')." Women's Hub", $landing);

        $this->assertStringContainsString("Women's Hub — About", str_replace(chr(194).chr(160), ' ', $this->get('/about')->assertOk()->getContent()));
    }

    public function test_the_bot_messages_use_the_new_name_in_every_language(): void
    {
        $registry = require base_path('resources/data/bot_messages.php');

        foreach (['start_info', 'link_success', 'moderation_approved', 'subscription_activated'] as $key) {
            $this->assertArrayHasKey($key, $registry['messages'], $key);

            foreach (['ru', 'en', 'ro'] as $locale) {
                $this->assertDoesNotMatchRegularExpression(self::OLD_NAMES, $registry['messages'][$key]['text'][$locale], "{$key}/{$locale}");
            }
        }

        // Ответ бота на любое сообщение называет платформу во всех языках.
        $info = $registry['messages']['start_info']['text'];
        foreach (['ru', 'en', 'ro'] as $locale) {
            $this->assertStringContainsString("Women's Hub", $info[$locale], $locale);
        }
    }
}
