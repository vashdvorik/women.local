<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

/**
 * Название платформы — «Women's hub». Прежнее «Women Entrepreneurs Platform of the Two Banks» (и его сокращённый
 * вид) нигде не должно остаться: ни в шаблонах, ни в текстах бота, ни в языковых файлах, ни в демо-данных.
 *
 * Не считается названием и потому разрешено: «Platform for Women Entrepreneurs» и «Платформа женщин предпринимателей» как
 * подзаголовок-пояснение под заголовком лендинга, слоганы о «двух берегах» и общая формулировка «a women entrepreneurs
 * platform» в подсказке для ИИ.
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
        $landing = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString("<title>Women's hub</title>", $landing);
        $this->assertStringContainsString("© ".date('Y')." Women's hub", $landing);

        $this->get('/about')->assertOk()->assertSee("Women's hub — About", false);
    }

    public function test_the_bot_messages_use_the_new_name_in_every_language(): void
    {
        $registry = require base_path('resources/data/bot_messages.php');

        foreach (['registration_welcome', 'login_link', 'moderation_approved'] as $key) {
            if (! isset($registry['messages'][$key])) {
                continue;
            }

            foreach (['ru', 'en', 'ro'] as $locale) {
                $this->assertDoesNotMatchRegularExpression(self::OLD_NAMES, $registry['messages'][$key]['text'][$locale], "{$key}/{$locale}");
            }
        }

        $welcome = $registry['messages']['registration_welcome']['text'];
        foreach (['ru', 'en', 'ro'] as $locale) {
            $this->assertStringContainsString("Women's hub", $welcome[$locale], $locale);
        }
    }
}
